<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger, } from '@/components/ui/dialog'
import Badge from '@/components/ui/badge/Badge.vue';
import { ChevronRight, SearchCode, ShieldCheck } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Welcome" />
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] lg:justify-center dark:bg-[#0a0a0a]">
        <section class="relative w-full px-6 py-16 lg:px-8 lg:py-24 overflow-hidden rounded-br-4xl"
            style="background-image: url('/images/clonewave-bg.jpg'); background-size: cover; background-position: center;">

            <div class="absolute inset-0 bg-black/20 dark:bg-black/50"></div>

            <header class="relative z-10 mb-12 flex w-full max-w-7xl mx-auto items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <!-- SVG Logo -->
                    <svg viewBox="0 0 200 200" class="h-10 w-10">
                        <path
                            d="M 100 45 C 70 45, 55 60, 55 80 C 55 95, 65 105, 80 110 C 95 115, 105 125, 105 140 C 105 150, 100 160, 90 165"
                            stroke="#3b82f6" stroke-width="12" stroke-linecap="round" fill="none" />
                        <circle cx="75" cy="65" r="28" fill="none" stroke="#06b6d4" stroke-width="10" />
                        <circle cx="75" cy="65" r="23" fill="#06b6d4" opacity="0.15" />
                        <circle cx="85" cy="55" r="8" fill="#e0f2fe" opacity="0.7" />
                        <circle cx="66" cy="58" r="4" fill="#e0f2fe" opacity="0.5" />
                    </svg>
                    <!-- App Name -->
                    <span class="text-white text-2xl tracking-tight font-bold">Signs</span>
                </div>
                <nav class="flex items-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="dashboard()"
                        class="inline-block rounded-sm border border-[#ffffff40] px-5 py-1.5 text-sm text-white hover:border-white">
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="login()"
                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm text-white hover:border-white">
                        Log in
                        </Link>
                        <Link :href="register()"
                            class="inline-block rounded-sm border border-[#ffffff40] px-5 py-1.5 text-sm text-white hover:border-white">
                        Register
                        </Link>
                    </template>
                </nav>
            </header>

            <!-- Hero content -->
            <div class="relative z-10 grid gap-12 lg:grid-cols-2 lg:gap-16 items-center max-w-7xl mx-auto">
                <div class="space-y-6 text-white">
                    <h1 class="text-5xl font-bold tracking-tight lg:text-6xl">
                        Detect Code Similarities with Precision
                    </h1>
                    <p class="text-lg max-w-xl">
                        Can't manage and analyze student submissions? Analyze quickly and detect similarities using
                        Signs' advanced logic-based detection system.
                    </p>
                    <Link :href="register()">
                    <Button size="lg" class="gap-2">
                        Get Started Now
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                    </Link>
                </div>
            </div>
        </section>


        <section class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16">
                    <div
                        class="inline-flex items-center justify-center px-4 py-2 mb-4 text-sm font-medium rounded-full bg-primary/10 text-primary">
                        Why Choose Us
                    </div>
                    <h2 class="text-4xl lg:text-5xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                        Powerful Detection Features
                    </h2>
                    <p class="text-lg text-[#6b6b6b] dark:text-[#a1a1a1] max-w-2xl mx-auto">
                        Advanced tools designed to help educators maintain academic integrity with confidence and
                        precision
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <Card
                        class="group border-[#19140020] dark:border-[#3E3E3A] hover:border-green-500/50 dark:hover:border-green-500/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-white dark:bg-[#1f1f1c]">
                        <CardHeader class="space-y-4">
                            <div
                                class="inline-flex h-16 w-16 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/20 group-hover:scale-110 transition-transform duration-300">
                                <ShieldCheck class="h-7 w-7 text-green-600 dark:text-green-500" />
                            </div>
                            <CardTitle class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                Logic-Based Detection
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                Goes beyond text matching by analyzing sequence flows, logic structures, and method
                                patterns to
                                identify true similarities
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card
                        class="group border-[#19140020] dark:border-[#3E3E3A] hover:border-primary/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-white dark:bg-[#1f1f1c]">
                        <CardHeader class="space-y-4">
                            <div
                                class="inline-flex h-16 w-16 items-center justify-center rounded-xl bg-primary/10 group-hover:scale-110 transition-transform duration-300">
                                <SearchCode class="h-7 w-7 text-primary" />
                            </div>
                            <CardTitle class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                Detailed Reports
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                Generates comprehensive similarity scores with highlighted logical matches for thorough
                                instructor review and decision-making
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card
                        class="group border-[#19140020] dark:border-[#3E3E3A] hover:border-amber-500/50 dark:hover:border-amber-500/50 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-white dark:bg-[#1f1f1c]">
                        <CardHeader class="space-y-4">
                            <div
                                class="inline-flex h-16 w-16 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/20 gap-1 group-hover:scale-110 transition-transform duration-300">
                                <img class="h-7 w-7 object-contain" src="/images/java-logo-png.png" alt="Java" />
                                <img class="h-7 w-7 object-contain" src="/images/python-logo-png.png" alt="Python" />
                            </div>
                            <CardTitle class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                Multi-Language Support
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                Optimized for Java and Python source code, ensuring accurate logic-based detection
                                across programming languages
                            </CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- How it Works Section -->
        <section class="mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-24">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    How It Works
                </h2>
                <p class="text-lg text-[#6b6b6b] dark:text-[#a1a1a1] max-w-2xl mx-auto">
                    Three simple steps to detect code similarities
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <div class="text-center space-y-4">
                    <div
                        class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary text-3xl font-bold text-white mb-2">
                        1
                    </div>
                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Submit Code
                    </h3>
                    <p class="text-[#6b6b6b] dark:text-[#a1a1a1]">
                        Students upload Java or Python source code using secure instructor-generated submission links
                    </p>
                </div>

                <div class="text-center space-y-4">
                    <div
                        class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary text-3xl font-bold text-white mb-2">
                        2
                    </div>
                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Logic Extraction
                    </h3>
                    <p class="text-[#6b6b6b] dark:text-[#a1a1a1]">
                        The system parses the code, extracting control flows, logic structures, and method patterns
                    </p>
                </div>

                <div class="text-center space-y-4">
                    <div
                        class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary text-3xl font-bold text-white mb-2">
                        3
                    </div>
                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Similarity Analysis
                    </h3>
                    <p class="text-[#6b6b6b] dark:text-[#a1a1a1]">
                        Compare logical structures between submissions and generate detailed similarity reports
                    </p>
                </div>
            </div>
        </section>

        <!-- Architecture Section -->
        <section class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16 items-center">
                    <div class="relative order-2 lg:order-1">
                        <div
                            class="aspect-video overflow-hidden rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 dark:from-primary/30 dark:to-primary/10">
                            <img src="/images/programming-bg.jpg" alt="System architecture"
                                class="h-full w-full object-cover dark:brightness-75" />
                        </div>
                    </div>
                    <div class="space-y-4 order-1 lg:order-2">
                        <h2 class="text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">
                            System Architecture
                        </h2>
                        <p class="text-lg text-[#6b6b6b] dark:text-[#a1a1a1] leading-relaxed">
                            The system integrates a Laravel-Inertia and Vue.js interface with a logic-based similarity
                            detection
                            service, powered by FastAPI microservices for efficient and scalable code analysis.
                        </p>
                        <Dialog>
                            <DialogTrigger as-child>
                                <Button variant="outline" size="lg" class="gap-2">
                                    View Technical Details
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-2xl">
                                <DialogHeader>
                                    <DialogTitle>Technical Stack</DialogTitle>
                                    <DialogDescription>
                                        The technologies powering Signs' code similarity detection system
                                    </DialogDescription>
                                </DialogHeader>
                                <div class="grid grid-cols-2 gap-6 py-4 mx-4">
                                    <a href="https://laravel.com" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="w-full flex flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/laravel-logo.png"
                                                alt="Laravel">
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Laravel
                                                </h3>
                                                <p class="text-xs sm:text-sm text-[#6b6b6b] dark:text-[#a1a1a1]">Backend
                                                    Framework
                                                </p>
                                            </div>
                                        </Badge>
                                    </a>

                                    <a href="https://fastapi.tiangolo.com" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="w-full flex flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/fastapi-logo.png"
                                                alt="FastAPI">
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">FastAPI
                                                </h3>
                                                <p class="text-xs sm:text-sm text-[#6b6b6b] dark:text-[#a1a1a1]">
                                                    Analysis
                                                    Service
                                                </p>
                                            </div>
                                        </Badge>
                                    </a>

                                    <a href="https://vuejs.org" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="w-full flex flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/vue-logo.png"
                                                alt="Vue.js">
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Vue.js</h3>
                                                <p class="text-xs sm:text-sm text-[#6b6b6b] dark:text-[#a1a1a1]">
                                                    Frontend
                                                    Framework
                                                </p>
                                            </div>
                                        </Badge>
                                    </a>

                                    <a href="https://inertiajs.com" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="w-full flex flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/inertia-logo.png"
                                                alt="Inertia.js">
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Inertia.js
                                                </h3>
                                                <p class="text-xs sm:text-sm text-[#6b6b6b] dark:text-[#a1a1a1]">SPA
                                                    Adapter</p>
                                            </div>
                                        </Badge>
                                    </a>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-[#19140010] dark:border-[#3E3E3A20] py-8">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <p class="text-center text-sm text-[#6b6b6b] dark:text-[#a1a1a1]">
                    © 2025 Signs. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
    <div class="hidden h-14.5 lg:block"></div>
</template>
