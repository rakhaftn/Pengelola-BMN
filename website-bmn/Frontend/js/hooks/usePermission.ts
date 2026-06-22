import { useAuth } from './useAuth';

export function usePermission() {
  const { user } = useAuth();

  const hasRole = (role: string | string[]): boolean => {
    if (!user?.roles) return false;
    const roles = Array.isArray(role) ? role : [role];
    return roles.some((r) => user.roles?.includes(r));
  };

  const isSuperAdmin = () => hasRole('super_admin');
  const isStaff = () => hasRole('staff');
  const isPeminjam = () => hasRole('user');

  const canCreateBarang = () => hasRole(['super_admin', 'staff']);
  const canEditBarang = () => hasRole(['super_admin', 'staff']);
  const canDeleteBarang = () => hasRole('super_admin');

  const canApprovePeminjaman = () => hasRole(['super_admin', 'staff']);
  const canProcessSerahTerima = () => hasRole(['super_admin', 'staff']);

  const canManageUsers = () => hasRole('super_admin');
  const canViewAuditLogs = () => hasRole('super_admin');
  const canDoStockOpname = () => hasRole(['super_admin', 'staff']);

  return {
    hasRole,
    isSuperAdmin,
    isStaff,
    isPeminjam,
    canCreateBarang,
    canEditBarang,
    canDeleteBarang,
    canApprovePeminjaman,
    canProcessSerahTerima,
    canManageUsers,
    canViewAuditLogs,
    canDoStockOpname,
  };
}
