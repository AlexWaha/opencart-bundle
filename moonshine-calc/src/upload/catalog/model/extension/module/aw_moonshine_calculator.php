<?php

/**
 * Moonshine Calculator Module - Model
 *
 * @author Alexander Vakhovski (AlexWaha)
 * @link https://alexwaha.com
 * @email support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwMoonshineCalculator extends Model
{
    public function getAllModuleData()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE code = '" . $this->moduleName . "' ORDER BY sort_order ASC");

        $modules = [];

        foreach ($query->rows as $result) {
            $modules[] = json_decode($result['setting'], true);
        }

        return $modules;
    }

    public function calculate($initial_strength, $final_strength, $moonshine_volume): array
    {
        $water_volume = ($initial_strength - $final_strength) * $moonshine_volume / $final_strength;
        $final_volume = $moonshine_volume + $water_volume;

        return [
            'water_volume' => round($water_volume, 2),
            'final_volume' => round($final_volume, 2),
            'initial_strength' => $initial_strength,
            'final_strength' => $final_strength,
            'moonshine_volume' => $moonshine_volume
        ];
    }

    public function getTemperatureCorrection($temperature)
    {
        $corrections = [
            10 => 1.013,
            15 => 1.006,
            20 => 1.000,
            25 => 0.994,
            30 => 0.988
        ];

        if (isset($corrections[$temperature])) {
            return $corrections[$temperature];
        }

        $keys = array_keys($corrections);
        sort($keys);

        foreach ($keys as $i => $temp) {
            if ($temperature <= $temp) {
                if ($i === 0) {
                    return $corrections[$temp];
                }

                $prev_temp = $keys[$i - 1];
                $ratio = ($temperature - $prev_temp) / ($temp - $prev_temp);

                return $corrections[$prev_temp] + $ratio * ($corrections[$temp] - $corrections[$prev_temp]);
            }
        }

        return $corrections[end($keys)];
    }

    public function validateStrength($strength)
    {
        return is_numeric($strength) && $strength >= 0 && $strength <= 100;
    }

    public function validateVolume($volume)
    {
        return is_numeric($volume) && $volume > 0;
    }
}
