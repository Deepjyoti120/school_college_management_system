import { RazorpayPaymentStatus } from "./enums";
import { FeeStructure } from "./FeeStructure";
import { School } from "./school";
import { SchoolClass } from "./SchoolClass";

export interface Payment {
  id: string;
  school_id: string;
  academic_year_id: string;
  class_id: string;
  fee_structure_id: string;
  user_id: string;
  month: number;
  year: number;
  status: RazorpayPaymentStatus;
  amount: number;
  gst_amount: number;
  total_amount: number;
  currency: string;
  payment_date: string;
  razorpay_order_id?: string;
  razorpay_payment_id?: string;
  razorpay_signature?: string;
  is_webhook: boolean;

  // Appended attributes
  amount_in_paise: number;
  status_color: string | null;
  status_label: string | null;

  fee_structure?: FeeStructure;
  school?: School;
  class?: SchoolClass;
}