<?php

namespace NetInventors\NetiNextEasyCoupon\Service\VoucherCodeGenerator\Validator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

class DuplicateValidator implements ValidatorInterface
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $voucherCode
     *
     * @return bool
     * @throws DBALException
     */
    public function validate(string $voucherCode): bool
    {
        $sql = '
            SELECT id
            FROM neti_easy_coupon
            WHERE code LIKE :code
              AND deleted = 0
        ';

        $id = $this->db->fetchOne(
            $sql,
            [
                'code' => $voucherCode,
            ]
        );

        if (true === is_string($id)) {
            return false;
        }

        $sql = '
            SELECT id
            FROM promotion_individual_code
            WHERE code LIKE :code
        ';

        $id = $this->db->fetchOne(
            $sql,
            [
                'code' => $voucherCode,
            ]
        );

        if (true === is_string($id)) {
            return false;
        }

        $sql = '
            SELECT id
            FROM promotion
            WHERE code LIKE :code
        ';

        $id = $this->db->fetchOne(
            $sql,
            [
                'code' => $voucherCode,
            ]
        );

        return !is_string($id);
    }
}
