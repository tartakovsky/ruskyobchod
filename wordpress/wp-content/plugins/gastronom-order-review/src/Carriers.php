<?php
declare(strict_types=1);
use Gastronom\OrderReview\Review;

function gor_carrier_values(WC_Order $order): array {
    $s=gor_state($order);
    if ($s['status'] !== 'approved') { throw new DomainException('Сначала утвердите заказ в блоке Gastronom.'); }
    if (in_array($order->get_status(), ['cancelled','refunded','failed','trash'], true)) { throw new DomainException('Отменённый заказ нельзя отправить.'); }
    $delivery=gor_plan($order, $s)['delivery'];
    $delivery['cod_cents']=$order->get_payment_method()==='cod' ? max(0, Review::balance($s)) : 0;
    return $delivery;
}

function gor_carrier_total(WC_Order $order) {
    if (!gor_managed($order)) { return $order->get_total(); }
    $s=gor_state($order);
    return Review::total($s['approved_quote'] ?: $s['quote'])/100;
}

function gor_gls_parcel(array $parcel, WC_Order $order): array {
    if (!gor_managed($order)) { return $parcel; }
    $delivery=gor_carrier_values($order);
    if ($delivery['cod_cents'] > 0) { $parcel['CODAmount']=$delivery['cod_cents']/100; }
    else { unset($parcel['CODAmount'], $parcel['CODReference']); }
    foreach ($parcel['ServiceList'] ?? [] as $i=>$service) {
        if (($service['Code'] ?? '') === 'INS') { $parcel['ServiceList'][$i]['INSParameter']['Value']=$delivery['value_cents']/100; }
    }
    // GLS currently has no parcel weight field. Do not invent unsupported API fields.
    return $parcel;
}

function gor_packeta_display($entity, WC_Order $order): void {
    if (!gor_managed($order)) { return; }
    $s=gor_state($order);
    if ($s['status'] !== 'approved') { return; }
    $d=gor_carrier_values($order);
    $entity->setCalculatedValue($d['value_cents']/100);
    $entity->setManualValue(null);
    $entity->setCalculatedCod($order->get_payment_method()==='cod' ? $d['cod_cents']/100 : null);
    $entity->setManualCod(null);
}

function gor_packeta_payload(array $data, $entity): array {
    // Entity::number is the internal order ID; customNumber is deliberately not used.
    $order=wc_get_order((int)$entity->getNumber());
    if (!$order || !gor_managed($order)) { return $data; }
    $d=gor_carrier_values($order);
    $data['value']=$d['value_cents']/100;
    $data['cod']=$d['cod_cents']/100;
    // Parcel weight remains owned by Packeta, including manual values and its defaults.
    // deliverOn means delivery date, not the date of handover to the courier.
    return $data;
}
