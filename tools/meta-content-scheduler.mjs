#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';

const GRAPH_VERSION = process.env.META_GRAPH_VERSION || 'v24.0';
const GRAPH_BASE = `https://graph.facebook.com/${GRAPH_VERSION}`;

function usage() {
  console.log(`Usage:
  node tools/meta-content-scheduler.mjs --plan docs/meta-content-schedule-2026-05-09.json [--execute] [--only POST_ID]
  node tools/meta-content-scheduler.mjs --plan docs/meta-content-schedule-2026-05-09.json --execute --token-from-clipboard

Default mode is dry-run. It validates the plan and prints the API operations that would be sent.

Required for --execute:
  META_PAGE_ID
  META_PAGE_ACCESS_TOKEN

This tool schedules Facebook Page photo posts. Instagram/Reels entries are reported
as blocked unless META_FACEBOOK_FALLBACK_FOR_INSTAGRAM=1 is set. With that fallback,
Instagram/Reels plan entries are scheduled as Facebook Page videos.
`);
}

function readArgs(argv) {
  const args = { execute: false, plan: '', only: '', tokenFromClipboard: false };
  for (let i = 2; i < argv.length; i += 1) {
    const arg = argv[i];
    if (arg === '--execute') args.execute = true;
    else if (arg === '--token-from-clipboard') args.tokenFromClipboard = true;
    else if (arg === '--plan') args.plan = argv[++i] || '';
    else if (arg === '--only') args.only = argv[++i] || '';
    else if (arg === '--help' || arg === '-h') args.help = true;
    else throw new Error(`Unknown argument: ${arg}`);
  }
  return args;
}

function pageTokenFromClipboard() {
  const clipboard = execFileSync('osascript', ['-e', 'the clipboard as text'], {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
  });
  const jsonMatch = clipboard.match(/"access_token"\s*:\s*"([^"]+)"/);
  if (jsonMatch) return jsonMatch[1].trim();
  const tokenMatch = clipboard.match(/\b(EAA[A-Za-z0-9_-]{40,})\b/);
  if (tokenMatch) return tokenMatch[1].trim();
  return '';
}

function unixTime(iso) {
  const ms = Date.parse(iso);
  if (!Number.isFinite(ms)) throw new Error(`Invalid scheduled_at: ${iso}`);
  return Math.floor(ms / 1000);
}

function localHour(iso) {
  const match = iso.match(/T(\d{2}):(\d{2}):/);
  if (!match) throw new Error(`scheduled_at must include time: ${iso}`);
  return { hour: Number(match[1]), minute: Number(match[2]) };
}

function recordedMetaId(post) {
  return post.meta_scheduled_post_id
    || post.meta_facebook_scheduled_post_id
    || post.meta_facebook_video_id
    || '';
}

function validatePost(post, { execute = false } = {}) {
  for (const key of ['id', 'platform', 'kind', 'scheduled_at', 'caption']) {
    if (!post[key]) throw new Error(`${post.id || 'post'} is missing ${key}`);
  }
  if (!['facebook', 'instagram'].includes(post.platform)) {
    throw new Error(`${post.id}: unsupported platform ${post.platform}`);
  }
  if (!['photo', 'video', 'reel'].includes(post.kind)) {
    throw new Error(`${post.id}: unsupported kind ${post.kind}`);
  }
  const { hour } = localHour(post.scheduled_at);
  if (hour < 9 || hour > 19) {
    throw new Error(`${post.id}: scheduled_at is outside allowed daytime window: ${post.scheduled_at}`);
  }
  const ts = unixTime(post.scheduled_at);
  const minFuture = Math.floor(Date.now() / 1000) + 600;
  if (ts < minFuture) {
    if (!execute && recordedMetaId(post)) return;
    throw new Error(`${post.id}: scheduled_at must be at least 10 minutes in the future`);
  }
  if (post.local_asset && !fs.existsSync(post.local_asset)) {
    throw new Error(`${post.id}: local asset does not exist: ${post.local_asset}`);
  }
}

function mimeType(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  if (ext === '.jpg' || ext === '.jpeg') return 'image/jpeg';
  if (ext === '.png') return 'image/png';
  if (ext === '.gif') return 'image/gif';
  if (ext === '.mp4') return 'video/mp4';
  return 'application/octet-stream';
}

function operationFor(post, env) {
  const scheduledPublishTime = String(unixTime(post.scheduled_at));
  const minFuture = Math.floor(Date.now() / 1000) + 600;
  if (Number(scheduledPublishTime) < minFuture && recordedMetaId(post)) {
    return {
      blocked: true,
      reason: `Already recorded in Meta with id ${recordedMetaId(post)}; not preparing a new API operation for a past timestamp.`,
    };
  }

  if (post.platform === 'facebook' && post.kind === 'photo') {
    if (!post.public_url && !post.local_asset) {
      return {
        blocked: true,
        reason: 'Facebook photo scheduling needs either local_asset or public_url.',
      };
    }
    return {
      method: 'POST',
      url: `${GRAPH_BASE}/${env.META_PAGE_ID}/photos`,
      multipart: Boolean(post.local_asset && !post.public_url),
      source_path: post.public_url ? '' : post.local_asset,
      body: {
        ...(post.public_url ? { url: post.public_url } : {}),
        caption: post.caption,
        published: 'false',
        scheduled_publish_time: scheduledPublishTime,
        access_token: env.META_PAGE_ACCESS_TOKEN,
      },
    };
  }

  if (
    (post.platform === 'facebook' && ['video', 'reel'].includes(post.kind)) ||
    (post.platform === 'instagram' && env.META_FACEBOOK_FALLBACK_FOR_INSTAGRAM === '1')
  ) {
    if (!post.local_asset) {
      return {
        blocked: true,
        reason: 'Facebook video scheduling needs local_asset.',
      };
    }
    return {
      method: 'POST',
      url: `${GRAPH_BASE}/${env.META_PAGE_ID}/videos`,
      multipart: true,
      source_path: post.local_asset,
      body: {
        description: post.caption,
        published: 'false',
        scheduled_publish_time: scheduledPublishTime,
        access_token: env.META_PAGE_ACCESS_TOKEN,
      },
    };
  }

  if (post.platform === 'instagram') {
    return {
      blocked: true,
      reason: 'Instagram/Reels cannot be prepared as a future scheduled Graph object here. Use an external scheduler, or run a publish-time job that creates and publishes the IG container at the target time.',
    };
  }

  return {
    blocked: true,
    reason: `${post.platform}/${post.kind} is not implemented yet`,
  };
}

async function postForm(operation) {
  let body;
  if (operation.multipart) {
    body = new FormData();
    for (const [key, value] of Object.entries(operation.body)) body.append(key, value);
    const file = new Blob([fs.readFileSync(operation.source_path)], { type: mimeType(operation.source_path) });
    body.append('source', file, path.basename(operation.source_path));
  } else {
    body = new URLSearchParams(operation.body);
  }
  const response = await fetch(operation.url, { method: operation.method, body });
  const json = await response.json().catch(() => ({}));
  if (!response.ok) {
    throw new Error(`Meta API error ${response.status}: ${JSON.stringify(json)}`);
  }
  return json;
}

async function main() {
  const args = readArgs(process.argv);
  if (args.help || !args.plan) {
    usage();
    process.exit(args.help ? 0 : 1);
  }

  const planPath = path.resolve(args.plan);
  const plan = JSON.parse(fs.readFileSync(planPath, 'utf8'));
  const posts = (plan.posts || []).filter((post) => !args.only || post.id === args.only);
  if (posts.length === 0) throw new Error(args.only ? `No post with id ${args.only}` : 'Plan has no posts');

  const env = {
    META_PAGE_ID: process.env.META_PAGE_ID || '',
    META_IG_USER_ID: process.env.META_IG_USER_ID || '',
    META_PAGE_ACCESS_TOKEN: process.env.META_PAGE_ACCESS_TOKEN || (args.tokenFromClipboard ? pageTokenFromClipboard() : ''),
    META_FACEBOOK_FALLBACK_FOR_INSTAGRAM: process.env.META_FACEBOOK_FALLBACK_FOR_INSTAGRAM || '',
  };

  for (const post of posts) validatePost(post, { execute: args.execute });

  if (args.execute) {
    if (!env.META_PAGE_ID) throw new Error('META_PAGE_ID is required for --execute');
    if (!env.META_PAGE_ACCESS_TOKEN) throw new Error('META_PAGE_ACCESS_TOKEN is required for --execute');
  } else {
    env.META_PAGE_ID ||= '<META_PAGE_ID>';
    env.META_PAGE_ACCESS_TOKEN ||= '<META_PAGE_ACCESS_TOKEN>';
  }

  for (const post of posts) {
    const operation = operationFor(post, env);
    console.log(`\n# ${post.id}`);
    console.log(`scheduled_at=${post.scheduled_at}`);
    if (operation.blocked) {
      console.log(`BLOCKED: ${operation.reason}`);
      continue;
    }
    const printable = {
      ...operation,
      body: { ...operation.body, access_token: env.META_PAGE_ACCESS_TOKEN ? '<redacted>' : '' },
    };
    console.log(JSON.stringify(printable, null, 2));
    if (args.execute) {
      const result = await postForm(operation);
      console.log(`RESULT ${post.id}: ${JSON.stringify(result)}`);
    }
  }
}

main().catch((error) => {
  console.error(`ERROR: ${error.message}`);
  process.exit(1);
});
