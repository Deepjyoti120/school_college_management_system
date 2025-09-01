export const enum UserRole {
  COM = 'com',               // Commercial
  GM = 'gm',                 // General Manager
  DEL = 'del',               // Delivery User
  FAC = 'fac',               // Facility
}

export const enum OrderStatus {
  PENDING = 'pending',
  REJECTED = 'rejected',
  APPROVED = 'approved',
  DISPATCHED = 'dispatched',
}

export type BadgeVariant = 'default' | 'destructive' | 'outline' | 'secondary';
export type StepperState = 'completed' | 'active' | 'inactive';