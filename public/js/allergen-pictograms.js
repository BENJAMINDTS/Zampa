/* Pictogramas oficiales de alérgenos — portados de allergens-oficiales.html.
   Cada entrada devuelve el contenido interno de un <svg viewBox="0 0 100 100">.
   Se inyecta en el badge con dangerouslySetInnerHTML.

   Convención: círculo sólido del color del alérgeno + anillo blanco interior,
   silueta blanca centrada. La variable CSS --c se usa para tintar sombras y
   detalles secundarios que comparten el color del alérgeno. */

(function () {
  const RING = `<circle cx="50" cy="50" r="48" fill="var(--c)"></circle>
                <circle cx="50" cy="50" r="43" fill="none" stroke="#fff" stroke-width="1.6" opacity="0.55"></circle>`;

  const PICTOGRAMS = {
    gluten: () => `${RING}
      <g fill="#fff">
        <rect x="48.2" y="32" width="3.6" height="42" rx="1.6"></rect>
        <g>
          <ellipse cx="40" cy="42" rx="4" ry="7" transform="rotate(-28 40 42)"></ellipse>
          <ellipse cx="60" cy="42" rx="4" ry="7" transform="rotate(28 60 42)"></ellipse>
          <ellipse cx="38" cy="52" rx="4" ry="7" transform="rotate(-28 38 52)"></ellipse>
          <ellipse cx="62" cy="52" rx="4" ry="7" transform="rotate(28 62 52)"></ellipse>
          <ellipse cx="36" cy="62" rx="4" ry="7" transform="rotate(-28 36 62)"></ellipse>
          <ellipse cx="64" cy="62" rx="4" ry="7" transform="rotate(28 64 62)"></ellipse>
        </g>
        <ellipse cx="50" cy="30" rx="3.2" ry="6"></ellipse>
      </g>`,

    crustaceos: () => `${RING}
      <g transform="translate(50 50) scale(0.86) translate(-50 -50)">
        <g fill="#fff">
          <ellipse cx="50" cy="58" rx="22" ry="13"></ellipse>
          <rect x="43" y="40" width="2.6" height="7" rx="1.3"></rect>
          <rect x="54.4" y="40" width="2.6" height="7" rx="1.3"></rect>
          <circle cx="44.3" cy="38" r="2.6"></circle>
          <circle cx="55.7" cy="38" r="2.6"></circle>
        </g>
        <g stroke="#fff" stroke-width="5" stroke-linecap="round" fill="none">
          <path d="M 32,50 Q 24,44 22,34"></path>
          <path d="M 68,50 Q 76,44 78,34"></path>
        </g>
        <g transform="translate(20 28) rotate(-30)">
          <ellipse cx="0" cy="0" rx="10" ry="7" fill="#fff"></ellipse>
          <path d="M 2,-2 L 11,-4 L 11,4 L 2,2 Z" fill="var(--c)"></path>
        </g>
        <g transform="translate(80 28) rotate(30)">
          <ellipse cx="0" cy="0" rx="10" ry="7" fill="#fff"></ellipse>
          <path d="M -2,-2 L -11,-4 L -11,4 L -2,2 Z" fill="var(--c)"></path>
        </g>
        <g stroke="#fff" stroke-width="3" stroke-linecap="round" fill="none">
          <path d="M 30,58 Q 18,60 12,70"></path>
          <path d="M 32,66 Q 20,72 16,82"></path>
          <path d="M 40,72 Q 36,80 38,86"></path>
          <path d="M 70,58 Q 82,60 88,70"></path>
          <path d="M 68,66 Q 80,72 84,82"></path>
          <path d="M 60,72 Q 64,80 62,86"></path>
        </g>
      </g>`,

    huevos: () => `${RING}
      <g>
        <g>
          <path d="M 30,39 C 20,40 19,49 19,55 C 19,65 23,71 30,71 C 37,71 41,65 41,55 C 41,49 40,40 30,39 Z" fill="#fff"></path>
          <path d="M 41,55 C 41,65 37,71 30,71 C 36,69 38,62 38,55 C 38,49 36,42 33,40 C 36,42 41,49 41,55 Z" fill="var(--c)" opacity="0.24"></path>
          <ellipse cx="24" cy="48" rx="2.4" ry="5" fill="#fff" transform="rotate(-28 24 48)"></ellipse>
        </g>
        <g>
          <path d="M 50,39 C 40,40 39,49 39,55 C 39,65 43,71 50,71 C 57,71 61,65 61,55 C 61,49 60,40 50,39 Z" fill="#fff"></path>
          <path d="M 61,55 C 61,65 57,71 50,71 C 56,69 58,62 58,55 C 58,49 56,42 53,40 C 56,42 61,49 61,55 Z" fill="var(--c)" opacity="0.24"></path>
          <ellipse cx="44" cy="48" rx="2.4" ry="5" fill="#fff" transform="rotate(-28 44 48)"></ellipse>
        </g>
        <g>
          <path d="M 70,39 C 60,40 59,49 59,55 C 59,65 63,71 70,71 C 77,71 81,65 81,55 C 81,49 80,40 70,39 Z" fill="#fff"></path>
          <path d="M 81,55 C 81,65 77,71 70,71 C 76,69 78,62 78,55 C 78,49 76,42 73,40 C 76,42 81,49 81,55 Z" fill="var(--c)" opacity="0.24"></path>
          <ellipse cx="64" cy="48" rx="2.4" ry="5" fill="#fff" transform="rotate(-28 64 48)"></ellipse>
        </g>
      </g>`,

    pescado: () => `${RING}
      <g fill="#fff">
        <path d="M14,50 Q28,28 52,30 Q68,32 74,50 Q68,68 52,70 Q28,72 14,50 Z"></path>
        <path d="M74,50 L86,38 L82,50 L86,62 Z"></path>
        <path d="M44,30 L52,18 L56,32 Z" opacity="0.85"></path>
        <path d="M28,38 Q24,50 28,62" fill="none" stroke="var(--c)" stroke-width="2" stroke-linecap="round"></path>
        <circle cx="22" cy="46" r="2.6" fill="var(--c)"></circle>
      </g>`,

    cacahuetes: () => `${RING}
      <g fill="#fff">
        <path d="M50,18
                 C 36,18 30,28 34,40
                 C 36,46 36,48 34,52
                 C 32,58 32,68 36,76
                 C 40,82 44,82 50,82
                 C 56,82 60,82 64,76
                 C 68,68 68,58 66,52
                 C 64,48 64,46 66,40
                 C 70,28 64,18 50,18 Z"></path>
      </g>
      <path d="M 66,40 C 64,48 64,48 66,52 C 68,58 68,68 64,76 C 60,82 56,82 50,82 C 56,80 60,76 62,68 C 64,58 62,52 62,50 C 62,48 64,42 64,32 C 64,26 60,20 56,18 C 62,18 70,28 66,40 Z" fill="var(--c)" opacity="0.16"></path>
      <g stroke="var(--c)" stroke-width="1.4" stroke-linecap="round" fill="none">
        <path d="M40,28 Q50,30 60,28"></path>
        <path d="M40,38 Q50,40 60,38"></path>
        <path d="M38,50 Q50,52 62,50"></path>
        <path d="M40,62 Q50,64 60,62"></path>
        <path d="M40,72 Q50,74 60,72"></path>
        <path d="M44,22 Q42,50 44,78"></path>
        <path d="M50,20 Q48,50 50,80"></path>
        <path d="M56,22 Q58,50 56,78"></path>
      </g>
      <ellipse cx="42" cy="30" rx="2.6" ry="5" fill="#fff" opacity="0.95" transform="rotate(-22 42 30)"></ellipse>`,

    soja: () => `${RING}
      <g fill="#fff">
        <path d="M 20,62
                 Q 20,50 32,50
                 Q 38,46 44,50
                 Q 50,54 56,50
                 Q 62,46 68,50
                 Q 80,50 80,62
                 Q 80,74 68,74
                 Q 62,78 56,74
                 Q 50,70 44,74
                 Q 38,78 32,74
                 Q 20,74 20,62 Z"></path>
        <path d="M 70,52 Q 62,44 54,36" stroke="#fff" stroke-width="2.4" fill="none" stroke-linecap="round"></path>
        <g>
          <ellipse cx="56" cy="30" rx="5" ry="2.8" transform="rotate(-30 56 30)"></ellipse>
          <ellipse cx="48" cy="28" rx="5" ry="2.8" transform="rotate(-70 48 28)"></ellipse>
          <ellipse cx="62" cy="24" rx="5" ry="2.8" transform="rotate(-10 62 24)"></ellipse>
        </g>
      </g>
      <g fill="var(--c)">
        <circle cx="38" cy="62" r="5.4"></circle>
        <circle cx="50" cy="62" r="5.8"></circle>
        <circle cx="62" cy="62" r="5.4"></circle>
      </g>
      <g fill="#fff" opacity="0.55">
        <ellipse cx="36" cy="60" rx="1.4" ry="2.4" transform="rotate(-30 36 60)"></ellipse>
        <ellipse cx="48" cy="60" rx="1.6" ry="2.6" transform="rotate(-30 48 60)"></ellipse>
        <ellipse cx="60" cy="60" rx="1.4" ry="2.4" transform="rotate(-30 60 60)"></ellipse>
      </g>`,

    lacteos: () => `${RING}
      <g fill="#fff">
        <rect x="35" y="40" width="30" height="40" rx="1.5"></rect>
        <path d="M 35,40 L 50,26 L 65,40 Z"></path>
      </g>
      <g stroke="var(--c)" stroke-width="1.2" fill="none" stroke-linecap="round" opacity="0.55">
        <line x1="50" y1="26" x2="50" y2="40"></line>
        <line x1="35" y1="40" x2="50" y2="33"></line>
        <line x1="65" y1="40" x2="50" y2="33"></line>
      </g>
      <rect x="59" y="40" width="6" height="40" fill="var(--c)" opacity="0.18"></rect>
      <path d="M 57,40 L 50,30 L 65,40 Z" fill="var(--c)" opacity="0.18"></path>
      <rect x="35" y="52" width="30" height="11" fill="var(--c)" opacity="0.7"></rect>
      <g fill="#fff">
        <circle cx="42" cy="57.5" r="1.4"></circle>
        <circle cx="50" cy="57.5" r="1.4"></circle>
        <circle cx="58" cy="57.5" r="1.4"></circle>
      </g>
      <g fill="#fff">
        <path d="M 76,52 C 72,56 72,62 76,66 C 80,62 80,56 76,52 Z"></path>
        <path d="M 80,72 C 78,74 78,77 80,79 C 82,77 82,74 80,72 Z" opacity="0.8"></path>
      </g>`,

    "frutos-cascara": () => `${RING}
      <g fill="#fff">
        <ellipse cx="50" cy="50" rx="28" ry="32"></ellipse>
      </g>
      <line x1="50" y1="20" x2="50" y2="80" stroke="var(--c)" stroke-width="2.4"></line>
      <g stroke="var(--c)" stroke-width="2" stroke-linecap="round" fill="none">
        <path d="M40,30 Q32,40 38,50 Q32,60 40,70"></path>
        <path d="M44,32 Q38,42 44,52 Q38,62 44,70"></path>
        <path d="M60,30 Q68,40 62,50 Q68,60 60,70"></path>
        <path d="M56,32 Q62,42 56,52 Q62,62 56,70"></path>
      </g>`,

    apio: () => `${RING}
      <g fill="#fff">
        <path d="M40,42 L36,82 L46,82 L46,42 Z"></path>
        <path d="M50,38 L46,84 L56,84 L56,38 Z" opacity="0.85"></path>
        <path d="M60,42 L56,82 L66,82 L62,42 Z"></path>
        <path d="M44,42 Q34,28 30,18 Q44,22 46,38 Z"></path>
        <path d="M52,38 Q50,22 56,12 Q62,24 56,38 Z"></path>
        <path d="M58,40 Q70,28 76,20 Q72,36 62,42 Z"></path>
      </g>`,

    mostaza: () => `${RING}
      <g fill="#fff">
        <rect x="38" y="22" width="24" height="8" rx="1.5"></rect>
        <rect x="42" y="30" width="16" height="6"></rect>
        <rect x="32" y="36" width="36" height="44" rx="3"></rect>
      </g>
      <rect x="36" y="48" width="28" height="20" fill="var(--c)" opacity="0.7"></rect>
      <text x="50" y="62" font-family="ui-sans-serif, system-ui, sans-serif" font-size="9" font-weight="900" fill="#fff" text-anchor="middle" letter-spacing="0.5">MUST</text>`,

    sesamo: () => `${RING}
      <g fill="#fff">
        <path d="M50,18 Q70,28 66,46 Q50,42 50,18 Z" opacity="0.85"></path>
        <path d="M50,18 Q30,28 34,46 Q50,42 50,18 Z" opacity="0.85"></path>
        <ellipse cx="36" cy="56" rx="5" ry="3.4" transform="rotate(-20 36 56)"></ellipse>
        <ellipse cx="50" cy="52" rx="5" ry="3.4"></ellipse>
        <ellipse cx="64" cy="56" rx="5" ry="3.4" transform="rotate(20 64 56)"></ellipse>
        <ellipse cx="42" cy="66" rx="5" ry="3.4" transform="rotate(15 42 66)"></ellipse>
        <ellipse cx="58" cy="66" rx="5" ry="3.4" transform="rotate(-15 58 66)"></ellipse>
        <ellipse cx="50" cy="76" rx="5" ry="3.4"></ellipse>
      </g>`,

    sulfitos: () => `${RING}
      <g fill="none" stroke="#fff">
        <polygon points="50,28 68,39 68,61 50,72 32,61 32,39" stroke-width="3.5" stroke-linejoin="round"></polygon>
        <polygon points="50,35 62,42 62,58 50,65 38,58 38,42" stroke-width="1.5" stroke-linejoin="round" opacity="0.5"></polygon>
      </g>
      <text x="50" y="50" text-anchor="middle" dominant-baseline="central" fill="#fff"
            font-family="ui-sans-serif, system-ui, sans-serif"
            font-weight="900" font-size="16" letter-spacing="0.5">E-X</text>`,

    moluscos: () => `${RING}
      <g transform="translate(2 2)">
        <path d="M48 72 L24 60 Q22 50 26 30 Q30 26 32 30 Q34 24 38 26 Q40 20 44 24 Q46 18 50 22 Q54 20 56 26 Q58 22 62 26 Q64 24 66 30 Q70 28 70 30 Q74 50 72 60 Z" fill="#fff"></path>
        <g stroke="var(--c)" stroke-width="1.6" stroke-linecap="round" fill="none">
          <path d="M48 70 L28 36"></path>
          <path d="M48 70 L36 24"></path>
          <path d="M48 70 L44 22"></path>
          <path d="M48 70 L52 22"></path>
          <path d="M48 70 L60 24"></path>
          <path d="M48 70 L68 36"></path>
        </g>
        <ellipse cx="48" cy="72" rx="6" ry="2.5" fill="#fff"></ellipse>
      </g>`,

    altramuces: () => `${RING}
      <g fill="#fff">
        <rect x="48.5" y="46" width="3" height="36" rx="1"></rect>
        <ellipse cx="50" cy="22" rx="6" ry="4"></ellipse>
        <ellipse cx="46" cy="28" rx="8" ry="4"></ellipse>
        <ellipse cx="54" cy="28" rx="8" ry="4"></ellipse>
        <ellipse cx="44" cy="36" rx="9" ry="4.5"></ellipse>
        <ellipse cx="56" cy="36" rx="9" ry="4.5"></ellipse>
        <ellipse cx="42" cy="44" rx="10" ry="4.5"></ellipse>
        <ellipse cx="58" cy="44" rx="10" ry="4.5"></ellipse>
        <path d="M50,68 Q34,72 28,82 Q42,80 50,72 Z"></path>
        <path d="M50,68 Q66,72 72,82 Q58,80 50,72 Z"></path>
      </g>`,
  };

  const ALLERGEN_COLORS = {
    gluten:           "#E27B2D",
    crustaceos:       "#4FAFD4",
    huevos:           "#F2D374",
    pescado:          "#1E3F7D",
    cacahuetes:       "#C97847",
    soja:             "#6DBE5A",
    lacteos:          "#6B3F2A",
    "frutos-cascara": "#7A3A24",
    apio:             "#95C93F",
    mostaza:          "#C68A1F",
    sesamo:           "#B5A082",
    sulfitos:         "#6E2A4D",
    moluscos:         "#6FB6D8",
    altramuces:       "#F0D04A",
  };

  function pictogramSVG(id) {
    const fn = PICTOGRAMS[id];
    if (!fn) return "";
    return fn();
  }

  window.ZAMPA_PICTOGRAMS = { PICTOGRAMS, ALLERGEN_COLORS, pictogramSVG };
})();
