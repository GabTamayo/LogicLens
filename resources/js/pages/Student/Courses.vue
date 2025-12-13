<script setup lang="ts">
import StudentAppLayout from "@/layouts/StudentAppLayout.vue";
import { BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Courses",
        href: "/student/courses",
    },
];
</script>

<template>

    <Head title="Courses" />

    <StudentAppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 lg:p-6">
            <div>
                <h1 class="text-lg sm:text-2xl font-bold tracking-tight cursor-default">
                    Enrolled Courses
                </h1>
            </div>
        </div>
    </StudentAppLayout>
</template>
