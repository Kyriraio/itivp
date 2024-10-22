<?php

namespace Application\Command;

use Application\Request\UpdateCoefficientRequest;
use Database\DBConnection as DB;
use Exception;

class UpdateCoefficientCommand {
    private DB $db;

    public function __construct() {
        $this->db = new DB();
    }

    /**
     * @throws Exception
     */
    public function execute(UpdateCoefficientRequest $request): string {
        $code = $request->getCode();
        $value = $request->getValue();

        // Check if the coefficient code exists in the database
        $sqlCheck = "SELECT COUNT(*) as count FROM coefficients WHERE code = :code";
        $count = $this->db->fetch($sqlCheck, [':code' => $code]);

        // If the code does not exist, throw an exception
        if ($count['count'] == 0) {
            throw new Exception('Invalid coefficient code: ' . $code);
        }

        // Update the coefficient in the database
        $sqlUpdate = "UPDATE coefficients SET value = :value WHERE code = :code";
        $this->db->execute($sqlUpdate, [
            ':value' => $value,
            ':code' => $code
        ]);

        return 'Coefficient updated successfully.';
    }


}
