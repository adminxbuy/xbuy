<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpecTemplate;
use App\Models\Category;

class SpecTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specsData = [
            'gpu' => [
                [
                    'spec_key' => 'vram_gb',
                    'spec_label' => 'VRAM',
                    'spec_type' => 'number',
                    'spec_unit' => 'GB',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 1,
                ],
                [
                    'spec_key' => 'memory_type',
                    'spec_label' => 'Memory Type',
                    'spec_type' => 'select',
                    'options' => ['GDDR5', 'GDDR6', 'GDDR6X'],
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 2,
                ],
                [
                    'spec_key' => 'cuda_cores',
                    'spec_label' => 'CUDA Cores / Stream Processors',
                    'spec_type' => 'number',
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 3,
                ],
                [
                    'spec_key' => 'tdp_watts',
                    'spec_label' => 'TDP',
                    'spec_type' => 'number',
                    'spec_unit' => 'Watts',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 4,
                ],
                [
                    'spec_key' => 'interface',
                    'spec_label' => 'Interface',
                    'spec_type' => 'select',
                    'options' => ['PCIe 3.0', 'PCIe 4.0'],
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 5,
                ],
                [
                    'spec_key' => 'display_outputs',
                    'spec_label' => 'Display Outputs',
                    'spec_type' => 'text',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 6,
                ],
                [
                    'spec_key' => 'length_mm',
                    'spec_label' => 'Card Length',
                    'spec_type' => 'number',
                    'spec_unit' => 'mm',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 7,
                ],
                [
                    'spec_key' => 'cooling_type',
                    'spec_label' => 'Cooling',
                    'spec_type' => 'select',
                    'options' => ['Dual Fan', 'Triple Fan', 'Single Fan', 'Blower'],
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 8,
                ]
            ],
            'cpu' => [
                [
                    'spec_key' => 'cores',
                    'spec_label' => 'Cores',
                    'spec_type' => 'number',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 1,
                ],
                [
                    'spec_key' => 'threads',
                    'spec_label' => 'Threads',
                    'spec_type' => 'number',
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 2,
                ],
                [
                    'spec_key' => 'base_clock_ghz',
                    'spec_label' => 'Base Clock',
                    'spec_type' => 'number',
                    'spec_unit' => 'GHz',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 3,
                ],
                [
                    'spec_key' => 'boost_clock_ghz',
                    'spec_label' => 'Boost Clock',
                    'spec_type' => 'number',
                    'spec_unit' => 'GHz',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 4,
                ],
                [
                    'spec_key' => 'socket',
                    'spec_label' => 'Socket',
                    'spec_type' => 'select',
                    'options' => ['LGA1700', 'LGA1200', 'AM4', 'AM5', 'LGA2066'],
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 5,
                ],
                [
                    'spec_key' => 'tdp_watts',
                    'spec_label' => 'TDP',
                    'spec_type' => 'number',
                    'spec_unit' => 'Watts',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 6,
                ],
                [
                    'spec_key' => 'cache_mb',
                    'spec_label' => 'Cache',
                    'spec_type' => 'number',
                    'spec_unit' => 'MB',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 7,
                ],
                [
                    'spec_key' => 'architecture',
                    'spec_label' => 'Architecture',
                    'spec_type' => 'text',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 8,
                ],
                [
                    'spec_key' => 'integrated_graphics',
                    'spec_label' => 'Integrated Graphics',
                    'spec_type' => 'boolean',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 9,
                ]
            ],
            'motherboard' => [
                [
                    'spec_key' => 'socket',
                    'spec_label' => 'CPU Socket',
                    'spec_type' => 'select',
                    'options' => ['LGA1700', 'LGA1200', 'AM4', 'AM5'],
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 1,
                ],
                [
                    'spec_key' => 'form_factor',
                    'spec_label' => 'Form Factor',
                    'spec_type' => 'select',
                    'options' => ['ATX', 'mATX', 'ITX', 'E-ATX'],
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 2,
                ],
                [
                    'spec_key' => 'chipset',
                    'spec_label' => 'Chipset',
                    'spec_type' => 'text',
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 3,
                ],
                [
                    'spec_key' => 'ram_slots',
                    'spec_label' => 'RAM Slots',
                    'spec_type' => 'number',
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 4,
                ],
                [
                    'spec_key' => 'max_ram_gb',
                    'spec_label' => 'Max RAM',
                    'spec_type' => 'number',
                    'spec_unit' => 'GB',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 5,
                ],
                [
                    'spec_key' => 'ram_type',
                    'spec_label' => 'RAM Type',
                    'spec_type' => 'select',
                    'options' => ['DDR4', 'DDR5'],
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 6,
                ],
                [
                    'spec_key' => 'pcie_slots',
                    'spec_label' => 'PCIe x16 Slots',
                    'spec_type' => 'number',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 7,
                ],
                [
                    'spec_key' => 'm2_slots',
                    'spec_label' => 'M.2 Slots',
                    'spec_type' => 'number',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 8,
                ],
                [
                    'spec_key' => 'sata_ports',
                    'spec_label' => 'SATA Ports',
                    'spec_type' => 'number',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 9,
                ]
            ],
            'ram' => [
                [
                    'spec_key' => 'capacity_gb',
                    'spec_label' => 'Capacity',
                    'spec_type' => 'number',
                    'spec_unit' => 'GB',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 1,
                ],
                [
                    'spec_key' => 'speed_mhz',
                    'spec_label' => 'Speed',
                    'spec_type' => 'number',
                    'spec_unit' => 'MHz',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 2,
                ],
                [
                    'spec_key' => 'ddr_type',
                    'spec_label' => 'DDR Type',
                    'spec_type' => 'select',
                    'options' => ['DDR3', 'DDR4', 'DDR5'],
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 3,
                ],
                [
                    'spec_key' => 'sticks',
                    'spec_label' => 'Number of Sticks',
                    'spec_type' => 'number',
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 4,
                ],
                [
                    'spec_key' => 'cl_latency',
                    'spec_label' => 'CAS Latency',
                    'spec_type' => 'number',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 5,
                ],
                [
                    'spec_key' => 'voltage',
                    'spec_label' => 'Voltage',
                    'spec_type' => 'number',
                    'spec_unit' => 'V',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 6,
                ],
                [
                    'spec_key' => 'form_factor',
                    'spec_label' => 'Form Factor',
                    'spec_type' => 'select',
                    'options' => ['DIMM', 'SO-DIMM'],
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 7,
                ],
                [
                    'spec_key' => 'rgb',
                    'spec_label' => 'RGB',
                    'spec_type' => 'boolean',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 8,
                ]
            ],
            'storage' => [
                [
                    'spec_key' => 'capacity_gb',
                    'spec_label' => 'Capacity',
                    'spec_type' => 'number',
                    'spec_unit' => 'GB',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 1,
                ],
                [
                    'spec_key' => 'type',
                    'spec_label' => 'Type',
                    'spec_type' => 'select',
                    'options' => ['SSD', 'NVMe', 'HDD'],
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 2,
                ],
                [
                    'spec_key' => 'interface',
                    'spec_label' => 'Interface',
                    'spec_type' => 'select',
                    'options' => ['SATA', 'M.2 PCIe 3.0', 'M.2 PCIe 4.0', 'USB'],
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 3,
                ],
                [
                    'spec_key' => 'read_speed',
                    'spec_label' => 'Read Speed',
                    'spec_type' => 'number',
                    'spec_unit' => 'MB/s',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 4,
                ],
                [
                    'spec_key' => 'write_speed',
                    'spec_label' => 'Write Speed',
                    'spec_type' => 'number',
                    'spec_unit' => 'MB/s',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 5,
                ],
                [
                    'spec_key' => 'form_factor',
                    'spec_label' => 'Form Factor',
                    'spec_type' => 'select',
                    'options' => ['2.5"', '3.5"', 'M.2'],
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 6,
                ],
                [
                    'spec_key' => 'health_percent',
                    'spec_label' => 'Disk Health',
                    'spec_type' => 'number',
                    'spec_unit' => '%',
                    'is_required' => false,
                    'is_highlighted' => true,
                    'sort_order' => 7,
                ]
            ],
            'psu' => [
                [
                    'spec_key' => 'wattage',
                    'spec_label' => 'Wattage',
                    'spec_type' => 'number',
                    'spec_unit' => 'W',
                    'is_required' => true,
                    'is_highlighted' => true,
                    'sort_order' => 1,
                ],
                [
                    'spec_key' => 'efficiency',
                    'spec_label' => 'Efficiency Rating',
                    'spec_type' => 'select',
                    'options' => ['80+ Bronze', '80+ Gold', '80+ Platinum', '80+ Titanium', 'None'],
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 2,
                ],
                [
                    'spec_key' => 'modular',
                    'spec_label' => 'Modularity',
                    'spec_type' => 'select',
                    'options' => ['Full Modular', 'Semi Modular', 'Non Modular'],
                    'is_required' => true,
                    'is_highlighted' => false,
                    'sort_order' => 3,
                ],
                [
                    'spec_key' => 'form_factor',
                    'spec_label' => 'Form Factor',
                    'spec_type' => 'select',
                    'options' => ['ATX', 'SFX'],
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 4,
                ],
                [
                    'spec_key' => 'fan_size_mm',
                    'spec_label' => 'Fan Size',
                    'spec_type' => 'number',
                    'spec_unit' => 'mm',
                    'is_required' => false,
                    'is_highlighted' => false,
                    'sort_order' => 5,
                ]
            ],
        ];

        foreach ($specsData as $catSlug => $specs) {
            $category = Category::where('slug', $catSlug)->first();
            if (!$category) {
                continue;
            }

            foreach ($specs as $spec) {
                SpecTemplate::create(array_merge($spec, [
                    'category_id' => $category->id
                ]));
            }
        }
    }
}
