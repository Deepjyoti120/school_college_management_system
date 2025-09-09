import { AcademicYear } from "./AcademicYear";
import { FeeType } from "./enums";
import { School } from "./school";

export interface FeeGenerate {
  id: string;
  school_id: string;
  academic_year_id: string;
  month: number;
  year: number;
  type: FeeType;

  school?: School;
  academicYear?: AcademicYear;
}
