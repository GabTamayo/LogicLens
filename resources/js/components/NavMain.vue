<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubItem, SidebarMenuSubButton } from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>

        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <SidebarMenuItem>
                    <SidebarMenuButton as-child :is-active="urlIsActive(item.href, page.url)" :tooltip="item.title">
                        <Link :href="item.href">
                        <component v-if="item.icon" :is="item.icon" />
                        <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>

                    <SidebarMenuSub v-if="item.items">
                        <SidebarMenuSubItem v-for="sub in item.items" :key="sub.title">
                            <SidebarMenuSubButton as-child :is-active="urlIsActive(sub.href, page.url)"
                                :tooltip="sub.title">
                                <Link :href="sub.href">
                                <component v-if="sub.icon" :is="sub.icon" />
                                <span>{{ sub.title }}</span>
                                </Link>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    </SidebarMenuSub>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
