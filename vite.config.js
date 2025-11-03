import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    //server: {
    //    host: "0.0.0.0", // Asegura que Vite sea accesible desde cualquier IP
    //    port: 5173, // Asegura que el puerto coincida con tu configuración
    //},

    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js", "resources/js/main.js"],
            refresh: true,
        }),
    ],
});
