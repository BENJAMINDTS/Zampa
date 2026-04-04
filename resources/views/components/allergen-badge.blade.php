@php
/**
 * Componente: allergen-badge
 *
 * Muestra el icono y nombre de un alérgeno según los 14 alérgenos de la UE.
 * El icono se selecciona por coincidencia de keywords en el nombre del ingrediente.
 *
 * @prop \App\Models\Ingredient $allergen
 */

$name = mb_strtolower($allergen->name);

$map = [
    '🌾' => ['gluten','trigo','cebada','centeno','avena','pan','harina','cereal','espelta','kamut'],
    '🦐' => ['crustáceo','crustaceo','gamba','langosta','cangrejo','langostino','bogavante','nécora','necora'],
    '🥚' => ['huevo','huevos','clara','yema'],
    '🐟' => ['pescado','atún','atun','salmón','salmon','merluza','bacalao','anchoa','sardina','trucha','dorada','lubina','boquerón'],
    '🥜' => ['cacahuete','maní','mani','groundnut'],
    '🫘' => ['soja','tofu','edamame'],
    '🥛' => ['lácteo','lacteo','leche','nata','queso','mantequilla','yogur','lactosa','caseína','caseina','requesón'],
    '🌰' => ['fruto seco','nuez','nueces','almendra','avellana','pistacho','anacardo','castaña','macadamia','pecana','nogal'],
    '🥬' => ['apio'],
    '🌻' => ['mostaza'],
    '🌱' => ['sésamo','sesamo','tahini'],
    '🍷' => ['sulfito','sulfitos','dióxido de azufre','so2'],
    '🌸' => ['altramuz','altramuces','lupino'],
    '🦪' => ['molusco','moluscos','almeja','mejillón','mejillon','ostra','calamar','pulpo','caracol','sepia'],
];

$icon = '⚠️';
foreach ($map as $emoji => $keywords) {
    foreach ($keywords as $keyword) {
        if (str_contains($name, $keyword)) {
            $icon = $emoji;
            break 2;
        }
    }
}
@endphp

<span role="listitem"
      title="{{ $allergen->name }}"
      class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full border border-red-200">
    <span aria-hidden="true">{{ $icon }}</span>
    <span>{{ $allergen->name }}</span>
</span>
