import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.js'],
        coverage: {
            provider: 'v8',
            include: ['resources/js/**/*.js'],
            exclude: ['resources/js/**/*.test.js', 'resources/js/bootstrap.js'],
            reporter: ['text', 'lcov'],
        },
    },
});
