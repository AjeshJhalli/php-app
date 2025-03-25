import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type Customer, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function Customer({ customer }: { customer: Customer }) {

  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Customers',
      href: '/customers',
    },
    {
      title: customer.name,
      href: `/customers/${customer.id}`,
    },
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Customers" />
      <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <Card>
          <CardHeader>
            <CardTitle>{customer.name}</CardTitle>
            <CardDescription>Some metadata about the customer</CardDescription>
          </CardHeader>
          <CardContent>
            <p>Some more data about the customer</p>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
