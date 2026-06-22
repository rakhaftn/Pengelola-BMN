import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Loader2 } from 'lucide-react';
import { DashboardStats } from '@/types';

export function Dashboard() {
  const { data: stats, isLoading } = useQuery<{ data: DashboardStats }>({
    queryKey: ['dashboard', 'stats'],
    queryFn: async () => {
      const response = await api.get('/dashboard/stats');
      return response.data;
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="w-8 h-8 animate-spin" />
      </div>
    );
  }

  const statCards = [
    {
      title: 'Total Barang',
      value: stats?.data?.total_barang || 0,
      subtitle: `${stats?.data?.barang_tersedia || 0} tersedia`,
      color: 'text-blue-600',
    },
    {
      title: 'Peminjaman Aktif',
      value: stats?.data?.peminjaman_dipinjam || 0,
      subtitle: `${stats?.data?.peminjaman_menunggu || 0} menunggu`,
      color: 'text-yellow-600',
    },
    {
      title: 'Terlambat',
      value: stats?.data?.peminjaman_terlambat || 0,
      subtitle: 'peminjaman',
      color: 'text-red-600',
    },
    {
      title: 'Barang Rusak',
      value: stats?.data?.barang_rusak || 0,
      subtitle: 'unit',
      color: 'text-orange-600',
    },
  ];

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold">Dashboard</h1>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {statCards.map((stat) => (
          <Card key={stat.title}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">{stat.title}</CardTitle>
            </CardHeader>
            <CardContent>
              <div className={`text-2xl font-bold ${stat.color}`}>
                {stat.value}
              </div>
              <p className="text-xs text-muted-foreground">{stat.subtitle}</p>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
