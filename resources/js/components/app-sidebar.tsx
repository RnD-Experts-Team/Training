import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    // BookOpen,
    ClipboardList,
    // FolderGit2,
    LayoutGrid,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as reportsIndex } from '@/routes/reports';
import { index as traineesIndex } from '@/routes/trainees';
import { index as sectionsIndex } from '@/routes/training/sections';
import type { Auth, NavItem } from '@/types';

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Trainees',
            href: traineesIndex(),
            icon: Users,
        },
        {
            title: 'Reports',
            href: reportsIndex(),
            icon: BarChart3,
        },
    ];

    if (auth.user.role === 'super_admin') {
        mainNavItems.push({
            title: 'Content Builder',
            href: sectionsIndex(),
            icon: ClipboardList,
        });
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
