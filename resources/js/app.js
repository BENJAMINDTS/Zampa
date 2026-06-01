import './bootstrap';

import Alpine from 'alpinejs';

import { registerLayoutBadges }  from './alpine/layout-badges.js';
import { registerKitchenPanel }  from './alpine/kitchen-panel.js';
import { registerBarComponents } from './alpine/bar-panel.js';
import { registerBusinessConfig } from './alpine/business-config.js';
import { registerCart }          from './alpine/cart.js';
import { registerMenuFilters }   from './alpine/menu-filters.js';
import { registerBill }          from './alpine/bill.js';
import { registerOrders }        from './alpine/orders.js';
import { registerChatWidget }    from './alpine/chat-widget.js';
import { registerTableMap }      from './pages/table-map.js';
import { registerLanding }       from './alpine/landing.js';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    registerLayoutBadges();
    registerKitchenPanel();
    registerBarComponents();
    registerBusinessConfig();
    registerCart();
    registerMenuFilters();
    registerBill();
    registerOrders();
    registerChatWidget();
    registerTableMap();
    registerLanding();
});

Alpine.start();
