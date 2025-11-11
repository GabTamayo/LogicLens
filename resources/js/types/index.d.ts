import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface PaginationData {
    current_page: number
    per_page: number
    total: number
    from: number
    to: number
    last_page: number
}


export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    items?: NavItem[];
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string; image: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Activity {
    id: number;
    title: string;
    language_text: string;
    user_id: number;
    created_at: string;
    open_links_count: number;
    closed_links_count: number;
}
export interface ActivityPagination extends PaginationData {
    data: Activity[]
}

export interface ActivityDetail {
    id: number
    title: string
    appUrl: string
    links: {
        data: ActivityLink[]
    } & PaginationData
}
export interface ActivityLink {
    id: number
    name: string
    token: string
    is_open: boolean
}

export interface SubmissionPageProps {
    bgImage: string
    name: string
    activityName: string
    token: string
    allowedExtensions: string
}

export type Submission = {
    activityId: number,
    activityTitle: string,
    link: ActivityLink,
    submissions: Array<{
        id: number,
        student_name: string,
        student_email: string,
        student_no: string,
        file_path: string,
        created_at: string,
        file_content?: string
        file_extension?: string
    }>
}

// Add these to your existing types.ts file

export interface DetectionRow {
    id: string
    submission_a: {
        id: number
        student_name: string
        student_no: string
        student_email: string
    }
    submission_b: {
        id: number
        student_name: string
        student_no: string
        student_email: string
    }
    similarity_score: number
    created_at: string
}

export interface DetectionPageProps {
    activityId: number
    activityTitle: string
    activityDate: string
    link: ActivityLink
    detections: PaginationData & {
        data: DetectionRow[]
        path: string
    }
    filters: Record<string, string>
}
