<script setup lang="ts">
import type { PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import type { KbdVariants } from '.';
import { reactiveOmit } from '@vueuse/core';
import { Primitive } from 'reka-ui';
import { cn } from '@/lib/utils';
import { kbdVariants } from '.';

const props = withDefaults(
    defineProps<
        PrimitiveProps & {
            variant?: KbdVariants['variant'];
            size?: KbdVariants['size'];
            class?: HTMLAttributes['class'];
        }
    >(),
    {
        as: 'kbd',
    },
);

const delegatedProps = reactiveOmit(props, 'class', 'variant', 'size');
</script>

<template>
    <Primitive data-slot="kbd" :class="cn(kbdVariants({ variant, size }), props.class)" v-bind="delegatedProps">
        <slot />
    </Primitive>
</template>
