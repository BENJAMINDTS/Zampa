import '../css/app.css';
import './bootstrap';

import Alpine from 'alpinejs';

import { registerLayoutBadges }   from './alpine/layout-badges.js';
import { registerKitchenPanel }   from './alpine/kitchen-panel.js';
import { registerBarComponents }  from './alpine/bar-panel.js';
import { registerBusinessConfig } from './alpine/business-config.js';
import { registerCart, registerVariantPicker } from './alpine/cart.js';
import { registerMenuFilters }    from './alpine/menu-filters.js';
import { registerBill, waiterCallWidget } from './alpine/bill.js';
import { registerOrders }         from './alpine/orders.js';
import { registerChatWidget }     from './alpine/chat-widget.js';
import { registerTableMap }       from './pages/table-map.js';
import { registerLanding }        from './alpine/landing.js';
import { registerVariantEditor }  from './alpine/variant-editor.js';
import { registerDailyMenuBanner } from './alpine/daily-menu-banner.js';
import { registerMenuStyle }      from './alpine/menu-style.js';
import { registerSchedule }       from './alpine/schedule.js';
import { registerTicketConfig }   from './alpine/ticket-config.js';
import { initProductReorder }     from './pages/product-reorder.js';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    registerSchedule();
    registerLayoutBadges();
    registerKitchenPanel();
    registerBarComponents();
    registerBusinessConfig();
    registerCart();
    registerVariantPicker();
    registerMenuFilters();
    registerBill();
    Alpine.data('waiterCallWidget', (hash) => waiterCallWidget(hash));
    registerOrders();
    registerChatWidget();
    registerTableMap();
    registerLanding();
    registerVariantEditor();
    registerDailyMenuBanner();
    registerMenuStyle();
    registerTicketConfig();
});

Alpine.start();

initProductReorder();
