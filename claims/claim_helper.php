<?php
function calculate_claim($fn, $an, $days, $km, $rate){
    $paper_total = ($fn + $an) * $rate['rate_per_paper'];
    $da = $days * $rate['da_per_day'];
    $ta = 2 * $km * $rate['ta_per_km'];

    return [
        'paper_amount'=>$paper_total,
        'da'=>$da,
        'ta'=>$ta,
        'total'=>$paper_total + $da + $ta
    ];
}
