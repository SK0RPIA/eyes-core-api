<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServerController extends Controller
{

    
    public function getRamData()
    {
        $output = null;
        $retval = null;

        // Exécute la commande vmstat et récupère la sortie
        exec('free', $output, $retval);

        if ($retval == 0) {
            // Traite la sortie pour obtenir les données du CPU
            $cpuData = $this->parseFreeOutput($output);
            return response()->json($cpuData);
        } else {
            // Retourne une réponse d'erreur si la commande échoue
            return response()->json(['error' => 'Could not retrieve CPU data'], 500);
        }
    }

    private function parseFreeOutput($output)
    {
        $systemData = [];

        foreach ($output as $line) {
            // Extraction des informations sur la mémoire (RAM) à partir de la sortie de la commande 'free'
            if (preg_match('/^Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $line, $matches)) {
                $systemData['total_memory_kb'] = intval($matches[1]);
                $systemData['used_memory_kb'] = intval($matches[2]);
                $systemData['free_memory_kb'] = intval($matches[3]);
            }

            // Extraction des informations sur le swap à partir de la sortie de la commande 'free'
            if (preg_match('/^Swap:\s+(\d+)\s+(\d+)\s+(\d+)/', $line, $matches)) {
                $systemData['total_swap_kb'] = intval($matches[1]);
                $systemData['used_swap_kb'] = intval($matches[2]);
                $systemData['free_swap_kb'] = intval($matches[3]);
            }
        }

        // Calcul des pourcentages d'utilisation de la mémoire et du swap
        if (isset($systemData['total_memory_kb']) && isset($systemData['used_memory_kb'])) {
            $systemData['memory_usage_percentage'] = ($systemData['used_memory_kb'] / $systemData['total_memory_kb']) * 100;
        }
        if (isset($systemData['total_swap_kb']) && isset($systemData['used_swap_kb'])) {
            $systemData['swap_usage_percentage'] = ($systemData['used_swap_kb'] / $systemData['total_swap_kb']) * 100;
        }

        return $systemData;
    }






}