<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BarChart3, ChevronRight, Code2, SearchCode, ShieldCheck, Upload } from 'lucide-vue-next';
</script>

<template>
    <Head title="Welcome" />
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] lg:justify-center dark:bg-[#0a0a0a]">
        <section
            class="relative w-full overflow-hidden px-6 py-16 lg:px-8 lg:py-24"
            style="background-image: url('/images/clonewave-bg.jpg'); background-size: cover; background-position: center"
        >
            <div class="absolute inset-0 bg-black/20 dark:bg-black/50"></div>

            <header class="relative z-10 mx-auto mb-12 flex w-full max-w-7xl items-center justify-between px-4 text-sm sm:px-6">
                <div class="flex items-center gap-2">
                    <svg viewBox="0 0 200 200" class="h-16 w-16 sm:h-20 sm:w-20">
                        <line x1="120" y1="170" x2="80" y2="110" stroke="#3b82f6" stroke-width="12" stroke-linecap="round" />
                        <circle cx="75" cy="65" r="35" fill="none" stroke="#06b6d4" stroke-width="10" />
                        <circle cx="75" cy="65" r="28" fill="#06b6d4" opacity="0.15" />
                        <circle cx="85" cy="55" r="10" fill="#e0f2fe" opacity="0.7" />
                        <circle cx="66" cy="58" r="5" fill="#e0f2fe" opacity="0.5" />
                    </svg>
                    <span class="text-xl font-bold tracking-tight text-white sm:text-2xl">LogicLens</span>
                </div>
                <nav class="flex items-center gap-2 sm:gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="$page.props.auth.user.is_student ? '/student/courses' : dashboard()"
                        class="inline-block rounded-sm border border-[#ffffff40] px-3 py-1.5 text-xs whitespace-nowrap text-white hover:border-white sm:px-5 sm:text-sm"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-block rounded-sm border border-transparent px-3 py-1.5 text-xs whitespace-nowrap text-white hover:border-white sm:px-5 sm:text-sm"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="inline-block rounded-sm border border-[#ffffff40] px-3 py-1.5 text-xs whitespace-nowrap text-white hover:border-white sm:px-5 sm:text-sm"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </header>

            <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div class="space-y-6 text-white">
                    <h1 class="text-5xl font-bold tracking-tight lg:text-6xl">Detect Code Similarities with Precision</h1>
                    <p class="max-w-xl text-lg">
                        Can't manage and analyze student submissions? Analyze quickly and detect similarities using LogicLens' advanced logic-based
                        detection system.
                    </p>
                    <Link :href="$page.props.auth.user ? ($page.props.auth.user.is_student ? '/student/courses' : dashboard()) : register()">
                        <Button size="lg" class="gap-2">
                            Get Started Now
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
                <svg class="relative block h-20 w-full md:h-28 lg:h-36" viewBox="0 0 1440 120" preserveAspectRatio="none">
                    <path d="M0,68 C300,100 600,40 900,70 C1150,90 1350,60 1440,80 L1440,120 L0,120 Z" fill="white" class="dark:fill-[#0a0a0a]">
                        <animate
                            attributeName="d"
                            dur="12s"
                            repeatCount="indefinite"
                            values="M0,68 C300,100 600,40 900,70 C1150,90 1350,60 1440,80 L1440,120 L0,120 Z;
                                    M0,64 C320,80 640,30 960,60 C1180,90 1380,70 1440,68 L1440,120 L0,120 Z;
                                    M0,72 C280,110 640,50 960,80 C1180,100 1380,60 1440,76 L1440,120 L0,120 Z;
                                    M0,68 C300,100 600,40 900,70 C1150,90 1350,60 1440,80 L1440,120 L0,120 Z"
                        />
                    </path>
                </svg>
            </div>
        </section>

        <section class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mb-16 space-y-8 text-center">
                    <Badge variant="outline" class="text-lg"> Why Choose Us </Badge>
                    <h2 class="mb-4 text-4xl font-bold tracking-tight text-[#1b1b18] lg:text-5xl dark:text-[#EDEDEC]">Powerful Detection Features</h2>
                    <p class="mx-auto max-w-2xl text-lg text-[#6b6b6b] dark:text-[#a1a1a1]">
                        Advanced tools designed to help educators maintain academic integrity with confidence and precision
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <Card
                        class="group border-[#19140020] bg-white transition-all duration-300 hover:-translate-y-1 hover:border-green-500/50 hover:shadow-xl dark:border-[#3E3E3A] dark:bg-[#1f1f1c] dark:hover:border-green-500/50"
                    >
                        <CardHeader class="space-y-4">
                            <div
                                class="inline-flex h-16 w-16 items-center justify-center rounded-xl bg-green-100 transition-transform duration-300 group-hover:scale-110 dark:bg-green-900/20"
                            >
                                <ShieldCheck class="h-7 w-7 text-green-600 dark:text-green-500" />
                            </div>
                            <CardTitle class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"> Logic-Based Detection </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                Goes beyond text matching by analyzing sequence flows, logic structures, and method patterns to identify true
                                similarities
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card
                        class="group border-[#19140020] bg-white transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-xl dark:border-[#3E3E3A] dark:bg-[#1f1f1c]"
                    >
                        <CardHeader class="space-y-4">
                            <div
                                class="inline-flex h-16 w-16 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <SearchCode class="h-7 w-7 text-primary" />
                            </div>
                            <CardTitle class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"> Detailed Reports </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                Generates comprehensive similarity scores with highlighted logical matches for thorough instructor review and
                                decision-making
                            </CardDescription>
                        </CardContent>
                    </Card>

                    <Card
                        class="group border-[#19140020] bg-white transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/50 hover:shadow-xl dark:border-[#3E3E3A] dark:bg-[#1f1f1c] dark:hover:border-amber-500/50"
                    >
                        <CardHeader class="space-y-4">
                            <div
                                class="inline-flex h-16 w-16 items-center justify-center gap-1 rounded-xl bg-amber-100 transition-transform duration-300 group-hover:scale-110 dark:bg-amber-900/20"
                            >
                                <img class="h-7 w-7 object-contain" src="/images/java-logo-png.png" alt="Java" />
                                <img class="h-7 w-7 object-contain" src="/images/python-logo-png.png" alt="Python" />
                            </div>
                            <CardTitle class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"> Multi-Language Support </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                Optimized for Java and Python source code, ensuring accurate logic-based detection across programming languages
                            </CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-24">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">How It Works</h2>
                <p class="mx-auto max-w-2xl text-lg text-[#6b6b6b] dark:text-[#a1a1a1]">Three simple steps to detect code similarities</p>
            </div>

            <div class="relative">
                <!-- Connecting line for desktop -->
                <div
                    class="absolute top-24 right-0 left-0 hidden h-0.5 bg-gradient-to-r from-primary/20 via-primary/40 to-primary/20 md:block dark:from-primary/30 dark:via-primary/50 dark:to-primary/30"
                ></div>

                <div class="relative grid gap-12 md:grid-cols-3">
                    <!-- Step 1 -->
                    <div class="group relative">
                        <div class="flex flex-col items-center space-y-6">
                            <div
                                class="relative inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/25 transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-primary/40"
                            >
                                <Upload class="h-10 w-10" />
                                <div
                                    class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-white text-sm font-bold text-primary shadow-md"
                                >
                                    1
                                </div>
                            </div>
                            <div class="space-y-3 text-center">
                                <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Submit Code</h3>
                                <p class="mx-auto max-w-xs text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                    Students upload Java or Python source code using secure instructor-generated submission links
                                </p>
                            </div>
                        </div>
                        <!-- Arrow for desktop -->
                        <div class="absolute top-12 right-0 hidden translate-x-1/2 text-primary/40 md:block dark:text-primary/50">
                            <ArrowRight class="h-6 w-6" />
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="group relative">
                        <div class="flex flex-col items-center space-y-6">
                            <div
                                class="relative inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/25 transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-primary/40"
                            >
                                <Code2 class="h-10 w-10" />
                                <div
                                    class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-white text-sm font-bold text-primary shadow-md"
                                >
                                    2
                                </div>
                            </div>
                            <div class="space-y-3 text-center">
                                <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Logic Extraction</h3>
                                <p class="mx-auto max-w-xs text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                                    The system parses the code, extracting control flows, logic structures, and method patterns
                                </p>
                            </div>
                        </div>
                        <!-- Arrow for desktop -->
                        <div class="absolute top-12 right-0 hidden translate-x-1/2 text-primary/40 md:block dark:text-primary/50">
                            <ArrowRight class="h-6 w-6" />
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="group relative">
                        <div class="flex flex-col items-center space-y-6">
                            <div
                                class="relative inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/25 transition-all duration-300 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-primary/40"
                            >
                                <BarChart3 class="h-10 w-10" />
                                <div
                                    class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-white text-sm font-bold text-primary shadow-md"
                                >
                                    3
                                </div>
                            </div>
                            <div class="space-y-3 text-center">
                                <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Similarity Analysis</h3>
                                <p class="mx-auto max-w-xs text-base leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
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
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <div class="relative order-2 lg:order-1">
                        <div
                            class="aspect-video overflow-hidden rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 dark:from-primary/30 dark:to-primary/10"
                        >
                            <img src="/images/programming-bg.jpg" alt="System architecture" class="h-full w-full object-cover dark:brightness-75" />
                        </div>
                    </div>
                    <div class="order-1 space-y-4 lg:order-2">
                        <h2 class="text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">System Architecture</h2>
                        <p class="text-lg leading-relaxed text-[#6b6b6b] dark:text-[#a1a1a1]">
                            The system integrates a Laravel-Inertia and Vue.js interface with a logic-based similarity detection service, powered by
                            FastAPI for efficient and scalable code analysis.
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
                                    <DialogDescription> The technologies powering LogicLens' code similarity detection system </DialogDescription>
                                </DialogHeader>
                                <div class="mx-4 grid grid-cols-2 gap-6 py-4">
                                    <a href="https://laravel.com" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="flex w-full flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/laravel-logo.png" alt="Laravel" />
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Laravel</h3>
                                                <p class="text-xs text-[#6b6b6b] sm:text-sm dark:text-[#a1a1a1]">Backend Framework</p>
                                            </div>
                                        </Badge>
                                    </a>

                                    <a href="https://fastapi.tiangolo.com" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="flex w-full flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/fastapi-logo.png" alt="FastAPI" />
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">FastAPI</h3>
                                                <p class="text-xs text-[#6b6b6b] sm:text-sm dark:text-[#a1a1a1]">Analysis Service</p>
                                            </div>
                                        </Badge>
                                    </a>

                                    <a href="https://vuejs.org" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="flex w-full flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/vue-logo.png" alt="Vue.js" />
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Vue.js</h3>
                                                <p class="text-xs text-[#6b6b6b] sm:text-sm dark:text-[#a1a1a1]">Frontend Framework</p>
                                            </div>
                                        </Badge>
                                    </a>

                                    <a href="https://inertiajs.com" target="_blank" rel="noopener noreferrer">
                                        <Badge variant="secondary" class="flex w-full flex-col items-center gap-3 p-6">
                                            <img class="h-12 w-12 object-contain" src="/images/inertia-logo.png" alt="Inertia.js" />
                                            <div class="text-center">
                                                <h3 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Inertia.js</h3>
                                                <p class="text-xs text-[#6b6b6b] sm:text-sm dark:text-[#a1a1a1]">SPA Adapter</p>
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
        <footer class="border-t border-[#19140010] py-8 dark:border-[#3E3E3A20]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <p class="text-center text-sm text-[#6b6b6b] dark:text-[#a1a1a1]">© 2025 LogicLens. All rights reserved.</p>
            </div>
        </footer>
    </div>
    <div class="hidden h-14.5 lg:block"></div>
</template>
