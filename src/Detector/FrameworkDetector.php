<?php

namespace Warnadi\DbStressmit\Detector;

class FrameworkDetector
{
    public static function detect(): string
    {
        // 1. Deteksi Laravel (cari class khasnya)
        if (class_exists('\Illuminate\Foundation\Application')) {
            return 'laravel';
        }

        // 2. Deteksi Symfony
        if (class_exists('\Symfony\Component\HttpKernel\Kernel')) {
            return 'symfony';
        }

        // 3. Deteksi WordPress (cek konstanta atau fungsi khas)
        if (defined('ABSPATH') || function_exists('add_action')) {
            return 'wordpress';
        }

        // 4. Deteksi CodeIgniter 4
        if (class_exists('\CodeIgniter\CodeIgniter')) {
            return 'codeigniter';
        }

        // 5. Fallback: mode CLI (standalone)
        return 'cli';
    }
}