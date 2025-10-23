import { FeeStructure } from "./FeeStructure";
import { User } from "./User";

export interface Discount {
  id: string;
  is_active: boolean;
  amount: number;
  fee_structure_id: string;
  user_id: string;
  user?: User;
  feeStructure?: FeeStructure;
  created_at: string;
  updated_at: string;
}
