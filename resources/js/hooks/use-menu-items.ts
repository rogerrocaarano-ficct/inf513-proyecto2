import { usePage } from '@inertiajs/react';
import {
    BookOpen,
    CalendarDays,
    ClipboardCheck,
    FilePlus,
    FileUp,
    Gavel,
    LayoutGrid,
    MessageSquareText,
    UserCheck,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { NavItem } from '@/types';

/**
 * Icons a menu option can name in MENU_ITEM.icon.
 */
const menuIcons: Record<string, LucideIcon> = {
    'book-open': BookOpen,
    'calendar-days': CalendarDays,
    'clipboard-check': ClipboardCheck,
    'file-plus': FilePlus,
    'file-up': FileUp,
    gavel: Gavel,
    'layout-grid': LayoutGrid,
    'message-square-text': MessageSquareText,
    'user-check': UserCheck,
    users: Users,
};

/**
 * The menu options the permissions of all the user's roles enable (FR-029, UC-018 BR-006).
 */
export function useMenuItems(): NavItem[] {
    const { menu } = usePage().props;

    return menu.map((option) => ({
        title: option.title,
        href: option.href,
        icon: option.icon ? (menuIcons[option.icon] ?? null) : null,
    }));
}
