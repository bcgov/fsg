import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import collectModuleAssetsPaths from './vite-module-loader.js';
import { resolve } from 'path';
import fs from 'fs';


const modulesPath = resolve(__dirname, 'Modules');
const moduleCSS = [];
const moduleVueFiles = [];
fs.readdirSync(modulesPath).forEach(moduleName => {
    const modulePath = resolve(modulesPath, moduleName);
    const cssPath = resolve(modulePath, 'resources/assets/css/app.css');
    if (fs.existsSync(cssPath)) {
        moduleCSS.push(cssPath);
    }

    // Add Vue files from modules
    const vueFiles = fs.readdirSync(resolve(modulePath, 'resources/assets/js')).filter(file => file.endsWith('.vue'));
    vueFiles.forEach(file => {
        moduleVueFiles.push(resolve(modulePath, 'resources/assets/js', file));
    });
});
async function getConfig() {
    const paths = [
        'resources/js/app.js',
    ];
    const allPaths = await collectModuleAssetsPaths(paths, 'Modules');


    return defineConfig({
        base: "/",
        plugins: [
            laravel({
                input: allPaths,
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: { '@': '/resources/js' }
        },
        server: {
            hmr: true,
            host: true,
            // port: 8031, // This is the port which we will use in docker
            origin: "http://127.0.0.1:8031",
            watch: {
                // Watch additional directories
                include: [
                    'resources/**',
                    ...moduleCSS.map(file => resolve(file)),
                    ...moduleVueFiles.map(file => resolve(file))
                ],
            },

        },
        // Thanks @sergiomoura for the window fix
        // add the next lines if you're using windows and hot reload doesn't work
        watch: {
            usePolling: true
        }
        // build: {
        //     outDir: 'public',
        //     rollupOptions: {
        //         output: {
        //             entryFileNames: 'js/[name].js',
        //             chunkFileNames: 'js/[name].js',
        //             assetFileNames: ({ name }) => {
        //                 if (/\.css$/.test(name ?? '')) {
        //                     return 'css/[name].[hash][extname]';
        //                 }
        //                 return 'assets/[name].[hash][extname]';
        //             },
        //         },
        //     },
        // },


    });

}

export default getConfig();
