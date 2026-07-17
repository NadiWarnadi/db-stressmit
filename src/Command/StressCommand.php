<?php

namespace Warnadi\DbStressmit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Warnadi\DbStressmit\Bootstrap;
use Warnadi\DbStressmit\Core\Scanner;
use Warnadi\DbStressmit\Core\RiskEngine;

class StressCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('stress')  // <-- INI YANG DITAMBAHKAN
            ->setDescription('Jalankan stress test pada database')
            ->addArgument('query', InputArgument::REQUIRED, 'SQL query yang akan di-stress')
            ->addOption('iterations', 'i', InputOption::VALUE_OPTIONAL, 'Jumlah iterasi', 100)
            ->addOption('concurrency', 'c', InputOption::VALUE_OPTIONAL, 'Jumlah koneksi paralel (simulasi)', 1)
            ->addOption('threshold', 't', InputOption::VALUE_OPTIONAL, 'Threshold slow query dalam ms', 200);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🔥 Db-Stressmit - Stress Test Sakti');

        // Ambil adapter sesuai framework terdeteksi
        $adapter = Bootstrap::getAdapter();
        $io->text('📌 Framework terdeteksi: ' . get_class($adapter));

        // Ambil parameter
        $query = $input->getArgument('query');
        $iterations = (int) $input->getOption('iterations');
        $concurrency = (int) $input->getOption('concurrency');
        $threshold = (int) $input->getOption('threshold');

        $io->section('⚙️  Konfigurasi');
        $io->table(
            ['Parameter', 'Nilai'],
            [
                ['Query', $query],
                ['Iterasi', $iterations],
                ['Concurrency (simulasi)', $concurrency],
                ['Threshold (ms)', $threshold],
            ]
        );

        // Inisialisasi Scanner
        $scanner = new Scanner($adapter);

        // Jalankan stress test
        $io->section('🚀 Menjalankan Stress Test...');
        $startOverall = microtime(true);
        $results = $scanner->run($query, $iterations, $concurrency);
        $totalTime = microtime(true) - $startOverall;

        // Tampilkan hasil dari Profiler
        $profiler = $scanner->getProfiler();
        $stats = $profiler->getStats();

        $io->section('📊 Hasil Statistik');
        $io->table(
            ['Metrik', 'Nilai'],
            [
                ['Total Eksekusi', $stats['count']],
                ['Min (ms)', number_format($stats['min'], 2)],
                ['Max (ms)', number_format($stats['max'], 2)],
                ['Avg (ms)', number_format($stats['avg'], 2)],
                ['Median (ms)', number_format($stats['median'], 2)],
                ['95th Percentile (ms)', number_format($stats['percentile_95'], 2)],
                ['Total Waktu (s)', number_format($totalTime, 2)],
                ['Error Rate', $stats['error_rate'] . '%'],
            ]
        );

        // Risk Engine
        $riskEngine = new RiskEngine($stats, $threshold);
        $riskScore = $riskEngine->calculateScore();
        $recommendations = $riskEngine->getRecommendations();

        $io->section('⚠️  Analisis Risiko');
        $io->writeln("Skor Risiko: <fg=red;options=bold>{$riskScore}/100</>");

        if (!empty($recommendations)) {
            $io->listing($recommendations);
        } else {
            $io->success('✨ Database Anda sehat! Tidak ada rekomendasi.');
        }

        return Command::SUCCESS;
    }
}