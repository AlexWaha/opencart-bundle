<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwStoreReviews extends Model
{
    public function getReview(int $reviewId): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_review` WHERE review_id = '" . (int)$reviewId . "'");

        return $query->row ?: [];
    }

    public function getReviews(array $data = []): array
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "aw_review` WHERE status = '1'";

        $sql .= " ORDER BY date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int) ($data['start'] ?? 0));
            $limit = max(1, (int) ($data['limit'] ?? 10));
            $sql .= " LIMIT " . $start . ", " . $limit;
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalReviews(): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "aw_review` WHERE status = '1'");

        return (int) ($query->row['total'] ?? 0);
    }

    public function addReview(array $data): int
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_review` SET
            author = '" . $this->db->escape($data['author']) . "',
            city = '" . $this->db->escape($data['city'] ?? '') . "',
            `text` = '" . $this->db->escape($data['text']) . "',
            rating = '" . (int) ($data['rating'] ?? 5) . "',
            status = '0',
            date_added = NOW()");

        return $this->db->getLastId();
    }
}
