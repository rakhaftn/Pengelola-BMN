import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import { usePermission } from '@/hooks/usePermission';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import { Plus, Search, Eye, Edit, Loader2 } from 'lucide-react';
import { BARANG_STATUS, BARANG_KONDISI } from '@/lib/constants';
import { Barang } from '@/types';

export function BarangList() {
  const navigate = useNavigate();
  const { canCreateBarang, canEditBarang } = usePermission();
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  const { data, isLoading } = useQuery<{ data: Barang[]; meta?: any }>({
    queryKey: ['barang', search, page],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (search) params.append('search', search);
      params.append('page', page.toString());
      params.append('per_page', '15');

      const response = await api.get(`/barang?${params}`);
      return response.data;
    },
  });

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      tersedia: 'bg-green-100 text-green-800',
      dipinjam: 'bg-blue-100 text-blue-800',
      perbaikan: 'bg-yellow-100 text-yellow-800',
      hilang: 'bg-gray-100 text-gray-800',
      dihapuskan: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">Barang</h1>
        {canCreateBarang() && (
          <Button onClick={() => navigate('/barang/create')}>
            <Plus className="w-4 h-4 mr-2" />
            Tambah Barang
          </Button>
        )}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Filter</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative max-w-sm">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              placeholder="Cari kode atau nama barang..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="pl-10"
            />
          </div>
        </CardContent>
      </Card>

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
                  <TableHead>Kode</TableHead>
                  <TableHead>Nama</TableHead>
                  <TableHead>Kategori</TableHead>
                  <TableHead>Kondisi</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data?.data?.map((barang) => (
                  <TableRow key={barang.id}>
                    <TableCell className="font-mono text-sm">
                      {barang.kode_barang}
                    </TableCell>
                    <TableCell>
                      <div>
                        <p className="font-medium">{barang.nama}</p>
                        <p className="text-sm text-muted-foreground">
                          {barang.merek}
                        </p>
                      </div>
                    </TableCell>
                    <TableCell>{barang.kategori?.nama}</TableCell>
                    <TableCell>
                      {BARANG_KONDISI[barang.kondisi as keyof typeof BARANG_KONDISI]}
                    </TableCell>
                    <TableCell>
                      <Badge className={getStatusColor(barang.status)}>
                        {BARANG_STATUS[barang.status as keyof typeof BARANG_STATUS]}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => navigate(`/barang/${barang.id}`)}
                        >
                          <Eye className="w-4 h-4" />
                        </Button>
                        {canEditBarang() && (
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() =>
                              navigate(`/barang/${barang.id}/edit`)
                            }
                          >
                            <Edit className="w-4 h-4" />
                          </Button>
                        )}
                      </div>
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
