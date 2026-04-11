#!/usr/bin/env python3

from __future__ import annotations

import json
import re
from pathlib import Path


ROOT = Path("/Users/alexandertartakovsky/Projects/gastronom-migration")
OUT_JSON = ROOT / ".plans" / "2026-04-10-custom-runtime-inventory.json"
OUT_MD = ROOT / ".plans" / "2026-04-10-custom-runtime-inventory.md"


def inventory_files() -> list[Path]:
    files = [
        ROOT / "gastronom-lang-switcher.live-current.php",
        ROOT / "plugins" / "gastronom-stock-fix" / "gastronom-stock-fix.php",
    ]

    files.extend(sorted((ROOT / "mu-plugins").glob("rusky-*.php")))
    return files


def extract_plugin_name(text: str, fallback: str) -> str:
    match = re.search(r"Plugin Name:\s*(.+)", text)
    return match.group(1).strip() if match else fallback


def extract_functions(text: str) -> list[str]:
    return sorted(set(re.findall(r"function\s+([a-zA-Z0-9_]+)\s*\(", text)))


def extract_hooks(text: str, hook_type: str) -> list[str]:
    pattern = rf"{hook_type}\(\s*['\"]([^'\"]+)['\"]"
    return sorted(set(re.findall(pattern, text)))


def extract_meta_keys(text: str) -> list[str]:
    candidates = set(re.findall(r"['\"](_[a-zA-Z0-9\-]+|dotypos_[a-zA-Z0-9_\-]+)['\"]", text))
    return sorted(candidates)


def classify(path: Path, text: str) -> list[str]:
    classes: list[str] = []
    low = text.lower()
    if "gastronom_lang" in text or "localize" in low or "translate" in low:
        classes.append("language")
    if "dotypos" in low:
        classes.append("dotypos")
    if "weight_preorder" in low or "_gls_weighted" in text:
        classes.append("weight-stock")
    if "woocommerce_" in low or "_stock" in text or "_price" in text:
        classes.append("commerce")
    if "template_redirect" in text or "ob_start" in text:
        classes.append("runtime-bootstrap")
    if "wp_footer" in text or "shop_loop" in low:
        classes.append("storefront-ui")
    if "sort_" in low or "orderby" in low:
        classes.append("catalog-sorting")
    if "option_active_plugins" in text:
        classes.append("plugin-runtime-override")
    if not classes:
        classes.append("misc")
    return sorted(set(classes))


def risk_score(text: str) -> str:
    score = 0
    if "option_active_plugins" in text:
        score += 3
    if "Dotypos::" in text or "dotyposService" in text:
        score += 3
    if "template_redirect" in text or "ob_start" in text:
        score += 2
    if "update_option(" in text or "wp_update_post(" in text:
        score += 1
    if "_stock" in text and "update_post_meta" in text:
        score += 2
    if "_gastronom_weight_preorder" in text:
        score += 2
    if score >= 6:
        return "high"
    if score >= 3:
        return "medium"
    return "low"


def main() -> None:
    inventory = []
    for path in inventory_files():
        text = path.read_text(encoding="utf-8")
        inventory.append({
            "path": str(path),
            "plugin_name": extract_plugin_name(text, path.name),
            "line_count": len(text.splitlines()),
            "function_count": len(extract_functions(text)),
            "functions": extract_functions(text),
            "actions": extract_hooks(text, "add_action"),
            "filters": extract_hooks(text, "add_filter"),
            "meta_keys": extract_meta_keys(text),
            "domains": classify(path, text),
            "risk": risk_score(text),
        })

    OUT_JSON.write_text(json.dumps(inventory, ensure_ascii=False, indent=2), encoding="utf-8")

    lines = ["# Custom Runtime Inventory", ""]
    for item in inventory:
        lines.append(f"## {item['plugin_name']}")
        lines.append("")
        lines.append(f"- Path: `{item['path']}`")
        lines.append(f"- Risk: `{item['risk']}`")
        lines.append(f"- Domains: `{', '.join(item['domains'])}`")
        lines.append(f"- Lines: `{item['line_count']}`")
        lines.append(f"- Functions: `{item['function_count']}`")
        lines.append(f"- Actions: `{', '.join(item['actions']) if item['actions'] else '(none)'}`")
        lines.append(f"- Filters: `{', '.join(item['filters']) if item['filters'] else '(none)'}`")
        lines.append(f"- Meta keys: `{', '.join(item['meta_keys']) if item['meta_keys'] else '(none)'}`")
        lines.append("")

    OUT_MD.write_text("\n".join(lines) + "\n", encoding="utf-8")
    print(OUT_MD)
    print(OUT_JSON)


if __name__ == "__main__":
    main()
