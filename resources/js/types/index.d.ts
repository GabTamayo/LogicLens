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
    current_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    last_page: number;
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
    is_student?: boolean;
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
    data: Activity[];
}

export interface ActivityDetail {
    id: number;
    title: string;
    language_text: string;
    content: string;
    appUrl: string;
    courses: Array<{
        id: string;
        name: string;
    }>;
    links: {
        data: ActivityLink[];
    } & PaginationData;
    test_cases: Array<{
        id: number;
        title: string;
        input: string;
        output: string;
        score: number;
    }>;
}

export interface Course {
    id: string;
    name: string;
    access_code: string;
    cover_photo: string;
    is_active: boolean;
    created_at: string;
    enrolled_at?: string;
    user?: {
        id: number;
        name: string;
    };
}

export interface CoursePagination extends PaginationData {
    data: Course[];
}

export interface CourseActivityLink {
    id: string;
    activity_id: string;
    activity_title: string;
    activity_language: string;
    token: string;
    is_open: boolean;
    expires_at: string | null;
    created_at: string;
    submissions_count: number;
    has_draft?: boolean;
}

export interface CourseShowProps {
    course: Course;
    activities?: {
        data: CourseActivityLink[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    students?: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            enrolled_at: string;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    activeTab: string;
}

export interface ActivityLink {
    id: number;
    course_id: string;
    course?: {
        id: string;
        name: string;
    };
    token: string;
    is_open: boolean;
    expires_at: string | null;
    submissions_count: number;
}

export interface SubmissionPageProps {
    bgImage: string;
    courseName: string;
    courseId: string;
    activityId: string;
    activityName: string;
    activityContent: string | null;
    token: string;
    language: string;
    languageText: string;
    studentName: string;
    studentEmail: string;
    hasSubmitted: boolean;
    draftCode: string | null;
    draftStdin: string | null;
    draftSavedAt: string | null;
    testCases: Array<{
        id: string;
        title: string;
        input: string;
        output: string;
        order: number;
    }>;
}

export type Submission = {
    activityId: number;
    activityTitle: string;
    link: ActivityLink;
    submissions: Array<{
        id: number;
        student_name: string;
        student_email: string;
        code_content: string;
        submitted_at: string;
        language: string;
    }>;
};
export interface DetectionRow {
    id: string;
    submission_a: {
        id: number;
        student_name: string;
        student_email: string;
    };
    submission_b: {
        id: number;
        student_name: string;
        student_email: string;
    };
    similarity_score: number;
    created_at: string;
}

export interface DetectionPageProps {
    activityId: number;
    activityTitle: string;
    activityDate: string;
    link: ActivityLink;
    detections: PaginationData & {
        data: DetectionRow[];
        path: string;
    };
    filters: Record<string, string>;
}

export interface DetectionShowProps {
    detection: {
        id: string;
        activity_link_id: string;
        seq_score: number;
        struct_score: number;
        avg_score: number;
        line_matches: Array<{
            code_a: [number, number];
            code_b: [number, number];
        }>;
        submission_a: {
            id: string;
            student_name: string;
            language: string;
        };
        submission_b: {
            id: string;
            student_name: string;
            language: string;
        };
    };
    fileA: string;
    fileB: string;
    aiExplanation?: string | null;
    explanationGeneratedAt?: string | null;
}

export interface ActiveLinksData {
    total: number;
    noDeadline: number;
    withDeadline: number;
}

export interface UpcomingThisWeek {
    id: string;
    activity_id: string;
    activity: string;
    language: string;
    course_id: string;
    course?: {
        id: string;
        name: string;
    };
    expires_at: string;
}

export interface UpcomingThisWeekPagination extends PaginationData {
    data: UpcomingThisWeek[];
}

export interface FlaggedDetections {
    id: string;
    link_id: string;
    activity_id: string;
    course_id: string;
    course?: {
        id: string;
        name: string;
    };
    activity: string;
    submitter_a: string;
    submitter_b: string;
    avg_score: number;
}

export interface FlaggedDetectionsPagination extends PaginationData {
    data: FlaggedDetections[];
}

export interface AverageScorePerActivity {
    activity_id: string;
    activity_title: string;
    language: string;
    average_score: number;
}

export interface AverageScorePerActivityLink {
    link_id: string;
    course?: {
        id: string;
        name: string;
    };
    average_score: number;
}

export interface DashboardPageProps {
    totalActivityLinks: number;
    totalLinksWithoutDetections: number;
    activeLinksData: ActiveLinksData;
    upcomingThisWeek: UpcomingThisWeekPagination;
    totalUpcomingThisWeek: number;
    flaggedDetections: FlaggedDetectionsPagination;
    totalFlaggedDetections: number;
    totalAverageScore: number;
    averageScorePerActivity: AverageScorePerActivity[];
}

export interface ActiveLink {
    id: string;
    activity_id: string;
    activity: string;
    language: string;
    course_id: string;
    course?: {
        id: string;
        name: string;
    };
    expires_at: string | null;
    has_deadline: boolean;
    created_at: string | null;
}

export interface PendingDetection {
    id: string;
    activity_id: string;
    activity: string;
    language: string | null;
    course_id: string;
    course?: {
        id: string;
        name: string;
    };
    created_at: string | null;
}

export interface ActiveLinksModalProps {
    activeLinks: ActiveLink[];
}

export interface EnrollCourseProps {
    errors?: {
        access_code?: string;
    };
}

export interface StudentCourseShowProps {
    course: Course;
    activities?: {
        data: CourseActivityLink[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    completedActivities?: {
        data: Array<{
            id: number;
            activity_id: number;
            activity_title: string;
            activity_language: string;
            token: string;
            is_open: boolean;
            submission_id?: string;
            score: number | null;
            total_score: number;
            created_at: string;
            submitted_at: string | null;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    students?: {
        data: Array<{
            id: number;
            name: string;
            email: string;
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    activeTab: string;
}
