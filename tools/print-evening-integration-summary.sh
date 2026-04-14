#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH='' cd -- "$(dirname -- "$0")/.." && pwd)"
PRODUCT_ID="${1:-10617}"

tmp_gate="$(mktemp)"
tmp_state="$(mktemp)"
trap 'rm -f "$tmp_gate" "$tmp_state"' EXIT

gate_status="green"
if ! "$ROOT_DIR/tools/verify-evening-integration-gate.sh" >"$tmp_gate" 2>&1; then
    gate_status="red"
fi

"$ROOT_DIR/tools/verify-dotypos-product-state.sh" "$PRODUCT_ID" >"$tmp_state"

extract_json_value() {
    key="$1"
    sed -n "s/^    \"$key\": \\(.*\\)\$/\\1/p" "$tmp_state" \
        | head -n 1 \
        | sed 's/,$//' \
        | sed 's/^"//' \
        | sed 's/"$//'
}

product_name="$(extract_json_value product_name)"
local_stock="$(extract_json_value local_stock)"
local_cash="$(extract_json_value local_cash_stock_kg)"
remote_stock="$(extract_json_value remote_stock_quantity_status)"
remote_match="$(extract_json_value remote_matches_local_cash)"

echo "evening integration summary"
echo "gate_status=$gate_status"
echo "product_id=$PRODUCT_ID"
echo "product_name=$product_name"
echo "local_stock=$local_stock"
echo "local_cash_stock_kg=$local_cash"
echo "remote_stock_quantity_status=$remote_stock"
echo "remote_matches_local_cash=$remote_match"
echo "deferred_local_only_mu=none"
echo "deferred_live_drift=rusky-commerce-adjustments.php"

if [ "$gate_status" = "green" ]; then
    echo "recommended_action=skip_mutation_unless_real_write_proof_is_required"
else
    echo "recommended_action=fix_gate_before_any_mutation"
fi
