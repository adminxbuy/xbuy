@extends('layouts.admin')

@section('title', 'Specification Templates')
@section('page_title', 'Specification Templates')

@section('content')
    <div class="space-y-6" x-data="specManager()">
        <!-- Top info bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <p class="text-sm text-zinc-500">Define custom specifications required or highlighted for listings in each
                    category.</p>
            </div>
            <button @click="openAddModal()"
                class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all flex items-center space-x-2 text-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Spec Field</span>
            </button>
        </div>

        <!-- Category selector tabs -->
        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-2 flex flex-wrap gap-1.5">
            @foreach($categories as $cat)
                <button type="button" @click="activeCategory = {{ $cat->id }}"
                    :class="activeCategory === {{ $cat->id }} ? 'bg-zinc-900 text-white hover:bg-zinc-800 text-black font-semibold shadow-sm border-black/10' : 'bg-white text-zinc-650 hover:bg-zinc-100 border-zinc-200'"
                    class="px-4 py-2 border rounded-lg text-xs transition-all focus:outline-none">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Specification fields table card -->
        <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
            <div class="p-5 border-b border-zinc-150 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="font-bold text-zinc-800 text-sm flex items-center">
                    <i data-lucide="sliders" class="w-4 h-4 mr-2 text-zinc-655"></i>
                    Category Specification Fields
                </h3>
                <span class="text-xs text-zinc-400 font-medium" x-text="getCurrentSpecsCount() + ' fields defined'"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-zinc-50 border-b border-zinc-150 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                            <th class="p-4 pl-6">Label / Key</th>
                            <th class="p-4">Input Type</th>
                            <th class="p-4">Unit</th>
                            <th class="p-4">Required</th>
                            <th class="p-4">Highlighted</th>
                            <th class="p-4">Validation Options</th>
                            <th class="p-4 pr-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        <template x-for="spec in getCurrentSpecs()" :key="spec.id">
                            <tr class="hover:bg-zinc-50/30 transition-colors text-sm text-zinc-700">
                                <!-- Label / Key -->
                                <td class="p-4 pl-6">
                                    <span class="font-bold text-zinc-850" x-text="spec.spec_label"></span>
                                    <p class="text-[10px] text-zinc-400 font-mono" x-text="spec.spec_key"></p>
                                </td>
                                <!-- Input Type -->
                                <td class="p-4 capitalize">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold" :class="{
                                              'bg-blue-50 text-blue-700 border border-blue-100': spec.spec_type === 'text',
                                              'bg-amber-50 text-amber-700 border border-amber-100': spec.spec_type === 'number',
                                              'bg-purple-50 text-purple-700 border border-purple-100': spec.spec_type === 'select',
                                              'bg-emerald-50 text-emerald-700 border border-emerald-100': spec.spec_type === 'boolean'
                                          }" x-text="spec.spec_type">
                                    </span>
                                </td>
                                <!-- Unit -->
                                <td class="p-4 font-mono text-zinc-500" x-text="spec.spec_unit || '-'"></td>
                                <!-- Required -->
                                <td class="p-4">
                                    <template x-if="spec.is_required">
                                        <span
                                            class="text-xs font-bold text-red-650 bg-red-50 border border-red-100 px-2 py-0.5 rounded-full">Yes</span>
                                    </template>
                                    <template x-if="!spec.is_required">
                                        <span class="text-xs text-zinc-400 px-2 py-0.5">No</span>
                                    </template>
                                </td>
                                <!-- Highlighted -->
                                <td class="p-4">
                                    <template x-if="spec.is_highlighted">
                                        <span
                                            class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">Yes</span>
                                    </template>
                                    <template x-if="!spec.is_highlighted">
                                        <span class="text-xs text-zinc-400 px-2 py-0.5">No</span>
                                    </template>
                                </td>
                                <!-- Validation Options -->
                                <td class="p-4">
                                    <template x-if="spec.spec_type === 'select' && spec.options">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            <template x-for="opt in spec.options" :key="opt">
                                                <span
                                                    class="text-[10px] bg-zinc-100 text-zinc-650 border border-zinc-200 px-1.5 py-0.5 rounded"
                                                    x-text="opt"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="spec.spec_type !== 'select'">
                                        <span class="text-xs text-zinc-400 italic">None</span>
                                    </template>
                                </td>
                                <!-- Actions -->
                                <td class="p-4 pr-6 text-right space-x-1">
                                    <button @click="openEditModal(spec)"
                                        class="p-2 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-xl transition-all"
                                        title="Edit Field">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <form :action="'/admin/spec-templates/' + spec.id" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this specification field?');">
                                        @csrf
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit"
                                            class="p-2 text-zinc-400 hover:text-red-650 hover:bg-red-50 rounded-xl transition-all"
                                            title="Delete Field">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="getCurrentSpecsCount() === 0">
                            <td colspan="7" class="p-12 text-center text-zinc-450 italic">
                                No specifications defined for this category. Click 'Add Spec Field' to start.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form (Add/Edit SpecTemplate) -->
        <div x-show="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto outline-none"
            x-cloak>
            <!-- Glassmorphism backdrop -->
            <div class="fixed inset-0 bg-zinc-950/65 backdrop-blur-md transition-opacity" @click="closeModal()"></div>

            <!-- Modal Content Card -->
            <div class="relative w-full max-w-lg mx-auto bg-white rounded-[28px] shadow-2xl border border-zinc-200/80 z-10 overflow-hidden"
                x-show="modalOpen" x-transition:enter="transition ease-out duration-350"
                x-transition:enter-start="opacity-0 scale-95 translate-y-6"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-6">

                <!-- Header -->
                <div class="px-6 py-5 border-b border-zinc-150 flex items-center justify-between bg-zinc-50/50">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800/15 flex items-center justify-center text-zinc-800">
                            <i data-lucide="sliders" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-zinc-900 text-base"
                            x-text="isEdit ? 'Edit Specification Field' : 'Add Specification Field'"></h4>
                    </div>
                    <button @click="closeModal()"
                        class="w-8 h-8 rounded-full hover:bg-zinc-100 flex items-center justify-center text-zinc-450 hover:text-zinc-800 transition-all focus:outline-none">
                        <i data-lucide="x" class="w-4.5 h-4.5"></i>
                    </button>
                </div>

                <form :action="isEdit ? '/admin/spec-templates/' + form.id : '{{ route('admin.spec-templates.store') }}'"
                    method="POST" class="p-6 space-y-5 text-sm">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="hidden" name="category_id" :value="activeCategory">

                    <!-- Spec Key -->
                    <div class="space-y-1.5" x-show="!isEdit">
                        <label for="modal_key" class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Unique
                            Spec Key</label>
                        <input type="text" id="modal_key" name="spec_key" x-model="form.spec_key" :required="!isEdit"
                            placeholder="e.g. vram_gb, memory_type, cores"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                    </div>

                    <!-- Spec Label -->
                    <div class="space-y-1.5">
                        <label for="modal_label"
                            class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Display Label</label>
                        <input type="text" id="modal_label" name="spec_label" x-model="form.spec_label" required
                            placeholder="e.g. VRAM Capacity, Memory Type, CPU Socket"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                    </div>

                    <!-- Input Type & Unit -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="modal_type"
                                class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Value Type</label>
                            <select id="modal_type" name="spec_type" x-model="form.spec_type" required
                                class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="select">Dropdown Select</option>
                                <option value="boolean">Yes/No Boolean</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="modal_unit"
                                class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Unit
                                (Optional)</label>
                            <input type="text" id="modal_unit" name="spec_unit" x-model="form.spec_unit"
                                placeholder="e.g. GB, MHz, W, mm"
                                class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Select Dropdown Options -->
                    <div class="space-y-1.5" x-show="form.spec_type === 'select'">
                        <label for="modal_options"
                            class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Select Options</label>
                        <input type="text" id="modal_options" name="options" x-model="form.options"
                            placeholder="e.g. DDR3, DDR4, DDR5 (comma separated)"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                        <p class="text-[10px] text-zinc-400 mt-1">Provide list options separated by commas.</p>
                    </div>

                    <!-- Toggles for Required and Highlighted -->
                    <div class="flex items-center space-x-6 p-3 bg-zinc-50 border border-zinc-150 rounded-xl">
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="is_required" value="1" x-model="form.is_required"
                                class="rounded text-[#09090b] focus:ring-zinc-950 border-zinc-300">
                            <span class="text-xs font-semibold text-zinc-700">Required Field</span>
                        </label>

                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="is_highlighted" value="1" x-model="form.is_highlighted"
                                class="rounded text-[#09090b] focus:ring-zinc-950 border-zinc-300">
                            <span class="text-xs font-semibold text-zinc-700">Highlight Spec (Hero area)</span>
                        </label>
                    </div>

                    <!-- Submit / Cancel -->
                    <div class="pt-5 border-t border-zinc-150 flex justify-end space-x-2">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2.5 border border-zinc-200 text-zinc-700 hover:bg-zinc-50 font-semibold rounded-xl text-xs transition-all active:scale-[0.98]">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold rounded-xl text-xs shadow-sm border border-black/10 transition-all active:scale-[0.98] hover:scale-[1.02]">
                            Save Field
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function specManager() {
            return {
                // Grouped specs from Laravel PHP
                groupedSpecs: @json($specTemplates),
                categories: @json($categories),
                activeCategory: {{ $categories->first() ? $categories->first()->id : 'null' }},
                modalOpen: false,
                isEdit: false,
                form: {
                    id: '',
                    spec_key: '',
                    spec_label: '',
                    spec_type: 'text',
                    spec_unit: '',
                    options: '',
                    is_required: false,
                    is_highlighted: false
                },

                init() {
                    this.$watch('modalOpen', val => {
                        setTimeout(() => lucide.createIcons(), 50);
                    });
                    setTimeout(() => lucide.createIcons(), 100);
                },

                getCurrentSpecs() {
                    if (this.activeCategory === null) return [];
                    return this.groupedSpecs[this.activeCategory] || [];
                },

                getCurrentSpecsCount() {
                    return this.getCurrentSpecs().length;
                },

                openAddModal() {
                    this.isEdit = false;
                    this.form = {
                        id: '',
                        spec_key: '',
                        spec_label: '',
                        spec_type: 'text',
                        spec_unit: '',
                        options: '',
                        is_required: false,
                        is_highlighted: false
                    };
                    this.modalOpen = true;
                },

                openEditModal(spec) {
                    this.isEdit = true;
                    this.form = {
                        id: spec.id,
                        spec_key: spec.spec_key,
                        spec_label: spec.spec_label,
                        spec_type: spec.spec_type,
                        spec_unit: spec.spec_unit || '',
                        options: Array.isArray(spec.options) ? spec.options.join(', ') : (spec.options || ''),
                        is_required: !!spec.is_required,
                        is_highlighted: !!spec.is_highlighted
                    };
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                }
            };
        }
    </script>
@endsection