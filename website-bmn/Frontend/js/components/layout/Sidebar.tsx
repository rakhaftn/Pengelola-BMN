import { Link, useLocation } from 'react-router-dom';
import { cn } from '@/lib/utils';
import { useAuth } from '@/hooks/useAuth';
import { usePermission } from '@/hooks/usePermission';
import {
  LayoutDashboard,
  Package,
  ArrowRightLeft,
  Users,
  FolderOpen,
  ClipboardList,
  FileText,
  ChevronDown,
} from 'lucide-react';
import { useState } from 'react';

const navigation = [
  { name: 'Dashboard', href: '/', icon: LayoutDashboard, roles: ['super_admin', 'staff', 'user'] },
  { name: 'Barang', href: '/barang', icon: Package, roles: ['super_admin', 'staff'] },
  { name: 'Peminjaman', href: '/peminjaman', icon: ArrowRightLeft, roles: ['super_admin', 'staff', 'user'] },
  { name: 'Users', href: '/users', icon: Users, roles: ['super_admin'] },
  {
    name: 'Master Data',
    href: '/master-data',
    icon: FolderOpen,
    roles: ['super_admin', 'staff'],
    children: [
      { name: 'Kategori Barang', href: '/master-data/kategori' },
      { name: 'Unit Kerja', href: '/master-data/unit-kerja' },
    ]
  },
  { name: 'Stock Opname', href: '/stock-opname', icon: ClipboardList, roles: ['super_admin', 'staff'] },
  { name: 'Audit Logs', href: '/audit-logs', icon: FileText, roles: ['super_admin'] },
];

export function Sidebar() {
  const location = useLocation();
  const { user } = useAuth();
  const { hasRole } = usePermission();
  const [expandedItems, setExpandedItems] = useState<string[]>([]);

  const toggleExpanded = (name: string) => {
    setExpandedItems((prev) =>
      prev.includes(name)
        ? prev.filter((n) => n !== name)
        : [...prev, name]
    );
  };

  const filteredNavigation = navigation.filter((item) =>
    item.roles.some((role) => hasRole(role))
  );

  return (
    <aside className="w-64 h-screen bg-sidebar border-r flex flex-col">
      <div className="p-4 border-b">
        <h1 className="text-xl font-bold">TRASET BMN</h1>
        <p className="text-sm text-muted-foreground">Admin Panel</p>
      </div>

      <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
        {filteredNavigation.map((item) => {
          const isActive =
            location.pathname === item.href ||
            (item.href !== '/' && location.pathname.startsWith(item.href + '/'));
          const isExpanded = expandedItems.includes(item.name);

          if (item.children) {
            return (
              <div key={item.name}>
                <button
                  onClick={() => toggleExpanded(item.name)}
                  className={cn(
                    'flex items-center justify-between w-full px-3 py-2 text-sm rounded-md',
                    'hover:bg-accent transition-colors',
                    isActive && 'bg-accent'
                  )}
                >
                  <div className="flex items-center gap-3">
                    <item.icon className="w-5 h-5" />
                    <span>{item.name}</span>
                  </div>
                  <ChevronDown
                    className={cn(
                      'w-4 h-4 transition-transform',
                      isExpanded && 'rotate-180'
                    )}
                  />
                </button>

                {isExpanded && (
                  <div className="ml-8 mt-1 space-y-1">
                    {item.children.map((child) => (
                      <Link
                        key={child.name}
                        to={child.href}
                        className={cn(
                          'block px-3 py-2 text-sm rounded-md',
                          'hover:bg-accent transition-colors',
                          location.pathname === child.href && 'bg-accent'
                        )}
                      >
                        {child.name}
                      </Link>
                    ))}
                  </div>
                )}
              </div>
            );
          }

          return (
            <Link
              key={item.name}
              to={item.href}
              className={cn(
                'flex items-center gap-3 px-3 py-2 text-sm rounded-md',
                'hover:bg-accent transition-colors',
                isActive && 'bg-accent'
              )}
            >
              <item.icon className="w-5 h-5" />
              <span>{item.name}</span>
            </Link>
          );
        })}
      </nav>
    </aside>
  );
}
