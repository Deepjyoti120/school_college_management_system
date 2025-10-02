export const enum UserRole {
  SUPER_ADMIN = 'super_admin',
  ADMIN = 'admin',
  STUDENT = 'student',
  TEACHER = 'teacher',
  ACCOUNTANT = 'accountant',
  LIBRARIAN = 'librarian',
  HEAD_MASTER = 'head_master',
  PRINCIPAL = 'principal',
  PARENT = 'parent',
  STAFF = 'staff',
}

export const enum OrderStatus {
  PENDING = 'pending',
  REJECTED = 'rejected',
  APPROVED = 'approved',
  DISPATCHED = 'dispatched',
}

export type BadgeVariant = 'default' | 'destructive' | 'outline' | 'secondary';
export type StepperState = 'completed' | 'active' | 'inactive';

export enum FeeType {
  ADMISSION = "admission",
  MONTHLY = "monthly",
}
export enum FrequencyType {
  ONE_TIME = 'one_time',
  MONTHLY = 'monthly',
  YEARLY = 'yearly',
}
export enum RazorpayPaymentStatus {
  PENDING = "pending",
  CAPTURED = "captured",
  FAILED = "failed",
  REFUNDED = "refunded",
  AUTHORIZED = "authorized",
  PAID = "paid",
}