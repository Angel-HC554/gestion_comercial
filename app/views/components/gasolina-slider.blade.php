<div class="mx-10 col-span-1" x-data="{ 
    model: {{ $gasolina ?? 0 }},
    init() {
        // Ensure the hidden input is updated when the component initializes
        if (this.$refs.gasolinaInput) {
            this.$refs.gasolinaInput.value = this.model;
            
            // Watch for changes to update the hidden input
            this.$watch('model', value => {
                if (this.$refs.gasolinaInput) {
                    this.$refs.gasolinaInput.value = value;
                }
            });
        }
    }
}">
    <input type="hidden" name="gasolina" x-ref="gasolinaInput" :value="model">
    <h3 class="mb-5 text-lg font-bold text-zinc-900 uppercase">SELECCIONE EL NIVEL DE GASOLINA:</h3>

    <div class="flex justify-center">
        <img class="p-2 h-36 w-auto object-contain" 
             :src="`/plantillas/tablero_gasolina/gasolina-${model}.png`"
             :alt="'Nivel de gasolina ' + model"
             onerror="this.style.display='none'"> 
    </div>

    <input name="gasolina" type="range" min="0" max="100" step="25"
           class="w-full h-2 bg-zinc-200 rounded-lg appearance-none cursor-pointer accent-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
           aria-label="range" 
           x-model="model" />
           
    <div class="w-full flex justify-between text-xs px-2 mt-2 font-semibold text-zinc-500">
        <span class="text-base">Vacio</span>
        <span class="text-base">1/4</span>
        <span class="text-base">1/2</span>
        <span class="text-base">3/4</span>
        <span class="text-base">Lleno</span>
    </div>
</div>