# Pages Directory

This directory contains page components and routing logic.

## Structure

Pages should be organized by feature or section:

```
pages/
├── index.tsx         # Home/Dashboard page
├── login.tsx         # Login page
├── dashboard/        # Dashboard pages
├── transactions/     # Transaction pages
├── users/            # User management pages
└── settings/         # Settings pages
```

## Routing

- Next.js uses file-based routing
- Each file in this directory becomes a route
- Dynamic routes use [param] syntax

## Example

```tsx
// pages/dashboard.tsx
import { NextPage } from 'next';

const Dashboard: NextPage = () => {
  return (
    <div>
      <h1>Dashboard</h1>
    </div>
  );
};

export default Dashboard;
```
