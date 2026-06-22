import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import { usePermission } from '@/hooks/usePermission';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Eye, Loader2, Plus } from 'lucide-react';
import { PEMINJAMAN_STATUS } from '@/lib/constants';
import { Peminjaman } from '@/types';

export function PeminjamanList() {
  const navigate = useNavigate();
  const { canApprovePeminjaman } = usePermission();
  const [page, setPage] = useState(1);

  const { data, isLoading } = useQuery<{ data: Peminjaman[]; meta?: any }>({
    queryKey: ['peminjaman', page],
    queryFn: async () => {
      const response = await api.get(`/peminjaman?page=${page}&per_page=15`);
      return response.data;
    },
  });

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      draft: 'bg-gray-100 text-gray-800',
      menunggu_persetujuan: 'bg-yellow-100 text-yellow-800',
      disetujui: 'bg-green-100 text-green-800',
      ditolak: 'bg-red-100 text-red-800',
      dipinjam: 'bg-blue-100 text-blue-800',
      dikembalikan: 'bg-indigo-100 text-indigo-800',
      selesai: 'bg-green-100 text-green-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">Peminjaman</h1>
        <Button onClick={() => navigate('/peminjaman/create')}>
          <Plus className="w-4 h-4 mr-2" />
          Ajukan Peminjaman
        </Button>
      </div>

      <Card>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="flex items-center justify-center h-64">
              <Loader2 className="w-8 h-8 animate-spin" />
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>No. Peminjaman</TableHead>
                  <TableHead>Peminjam</TableHead>
                  <TableHead>Tanggal Pinjam</TableHead>
                  <TableHead>Tanggal Kembali</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data?.data?.map((item) => (
                  <TableRow key={item.id}>
                    <TableCell className="font-mono">
                      {item.nomor_peminjaman}
                    </TableCell>
                    <TableCell>{item.peminjam?.name}</TableCell>
                    <TableCell>
                      {item.tanggal_pinjam
                        ? new Date(item.tanggal_pinjam).toLocaleDateString(
                            'id-ID'
                          )
                        : '-'}
                    </TableCell>
                    <TableCell>
                      {item.tanggal_kembali_rencana
                        ? new Date(
                            item.tanggal_kembali_rencana
                          ).toLocaleDateString('id-ID')
                        : '-'}
                    </TableCell>
                    <TableCell>
                      <Badge className={getStatusColor(item.status)}>
                        {PEMINJAMAN_STATUS[item.status as keyof typeof PEMINJAMAN_STATUS]}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => navigate(`/peminjaman/${item.id}`)}
                      >
                        <Eye className="w-4 h-4" />
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>

        {data?.meta && (
          <div className="flex items-center justify-between p-4 border-t">
            <p className="text-sm text-muted-foreground">
              Halaman {data.meta.current_page} dari {data.meta.last_page}
            </p>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
              >
                Previous
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={() => setPage((p) => p + 1)}
                disabled={page >= data.meta.last_page}
              >
                Next
              </Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}
