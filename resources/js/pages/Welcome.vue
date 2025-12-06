<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger, } from '@/components/ui/dialog'
import Badge from '@/components/ui/badge/Badge.vue';
import { ChevronRight, SearchCode, ShieldCheck, Upload, Code2, BarChart3, ArrowRight } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Welcome" />
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] lg:justify-center dark:bg-[#0a0a0a]">
        <section class="relative w-full px-6 py-16 lg:px-8 lg:py-24 overflow-hidden"
            style="background-image: url('/images/clonewave-bg.jpg'); background-size: cover; background-position: center;">

            <div class="absolute inset-0 bg-black/20 dark:bg-black/50"></div>

            <header class="relative z-10 mb-12 flex w-full max-w-7xl mx-auto items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <svg viewBox="0 0 200 200" class="h-20 w-20">
                        <line x1="120" y1="170" x2="80" y2="110" stroke="#3b82f6" stroke-width="12"
                            stroke-linecap="round" />
                        <circle cx="75" cy="65" r="35" fill="none" stroke="#06b6d4" stroke-width="10" />
                        <circle cx="75" cy="65" r="28" fill="#06b6d4" opacity="0.15" />
                        <circle cx="85" cy="55" r="10" fill="#e0f2fe" opacity="0.7" />
                        <circle cx="66" cy="58" r="5" fill="#e0f2fe" opacity="0.5" />
                    </svg>
                    <span class="text-white text-2xl tracking-tight font-bold">LogicLens</span>
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

            <div class="relative z-10 grid gap-12 lg:grid-cols-2 lg:gap-16 items-center max-w-7xl mx-auto">
                <div class="space-y-6 text-white">
                    <h1 class="text-5xl font-bold tracking-tight lg:text-6xl">
                        Detect Code Similarities with Precision
                    </h1>
                    <p class="text-lg max-w-xl">
                        Can't manage and analyze student submissions? Analyze quickly and detect similarities using
                        LogicLens' advanced logic-based detection system.
                    </p>
                    <Link :href="register()">
                    <Button size="lg" class="gap-2">
                        Get Started Now
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                    </Link>
                </div>
            </div>

            <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
                <svg class="relative block w-full h-20 md:h-28 lg:h-36" viewBox="0 0 1440 120"
                    preserveAspectRatio="none">
                    <path d="M0,68 C300,100 600,40 900,70 C1150,90 1350,60 1440,80 L1440,120 L0,120 Z" fill="white"
                        class="dark:fill-[#0a0a0a]">
                        <animate attributeName="d" dur="12s" repeatCount="indefinite" values="M0,68 C300,100 600,40 900,70 C1150,90 1350,60 1440,80 L1440,120 L0,120 Z;
                                    M0,64 C320,80 640,30 960,60 C1180,90 1380,70 1440,68 L1440,120 L0,120 Z;
                                    M0,72 C280,110 640,50 960,80 C1180,100 1380,60 1440,76 L1440,120 L0,120 Z;
                                    M0,68 C300,100 600,40 900,70 C1150,90 1350,60 1440,80 L1440,120 L0,120 Z" />
                    </path>
                </svg>
            </div>
        </section>


        <section class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center mb-16 space-y-8">
                    <Badge variant="outline" class="text-lg">
                        Why Choose Us
                    </Badge>
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

        <section class="mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-24">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    How It Works
                </h2>
                <p class="text-lg text-[#6b6b6b] dark:text-[#a1a1a1] max-w-2xl mx-auto">
                    Three simple steps to detect code similarities
                </p>
            </div>

            <div class="relative">
                <!-- Connecting line for desktop -->
                <div class="hidden md:block absolute top-24 left-0 right-0 h-0.5 bg-gradient-to-r from-primary/20 via-primary/40 to-primary/20 dark:from-primary/30 dark:via-primary/50 dark:to-primary/30"></div>

                <div class="grid gap-12 md:grid-cols-3 relative">
                    <!-- Step 1 -->
                    <div class="relative group">
                        <div class="flex flex-col items-center space-y-6">
                            <div
                                class="relative inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/25 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-primary/40 transition-all duration-300">
                                <Upload class="h-10 w-10" />
                                <div
                                    class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-white text-primary text-sm font-bold shadow-md">
                                    1
                                </div>
                            </div>
                            <div class="text-center space-y-3">
                                <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    Submit Code
                                </h3>
                                <p class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1] max-w-xs mx-auto">
                                    Students upload Java or Python source code using secure instructor-generated submission links
                                </p>
                            </div>
                        </div>
                        <!-- Arrow for desktop -->
                        <div class="hidden md:block absolute top-12 right-0 translate-x-1/2 text-primary/40 dark:text-primary/50">
                            <ArrowRight class="h-6 w-6" />
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative group">
                        <div class="flex flex-col items-center space-y-6">
                            <div
                                class="relative inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/25 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-primary/40 transition-all duration-300">
                                <Code2 class="h-10 w-10" />
                                <div
                                    class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-white text-primary text-sm font-bold shadow-md">
                                    2
                                </div>
                            </div>
                            <div class="text-center space-y-3">
                                <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    Logic Extraction
                                </h3>
                                <p class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1] max-w-xs mx-auto">
                                    The system parses the code, extracting control flows, logic structures, and method patterns
                                </p>
                            </div>
                        </div>
                        <!-- Arrow for desktop -->
                        <div class="hidden md:block absolute top-12 right-0 translate-x-1/2 text-primary/40 dark:text-primary/50">
                            <ArrowRight class="h-6 w-6" />
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative group">
                        <div class="flex flex-col items-center space-y-6">
                            <div
                                class="relative inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/25 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-primary/40 transition-all duration-300">
                                <BarChart3 class="h-10 w-10" />
                                <div
                                    class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-white text-primary text-sm font-bold shadow-md">
                                    3
                                </div>
                            </div>
                            <div class="text-center space-y-3">
                                <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    Similarity Analysis
                                </h3>
                                <p class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1] max-w-xs mx-auto">
                                    Compare logical structures between submissions and generate detailed similarity reports
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
                            detection service, powered by FastAPI for efficient and scalable code analysis.
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
                                        The technologies powering LogicLens' code similarity detection system
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
                    © 2025 LogicLens. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
    <div class="hidden h-14.5 lg:block"></div>
</template>
