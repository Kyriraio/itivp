<?php

namespace Application\Request;

class PlaceBetRequest {
    private int $betId;
    private float $amount;
    private int $outcome;

    public function __construct(int $betId, float $amount, int $outcome) {
        $this->betId = $betId;
        $this->amount = $amount;
        $this->outcome = $outcome;
    }

    public function getBetId(): int {
        return $this->betId;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getOutcome(): int {
        return $this->outcome;
    }
}
