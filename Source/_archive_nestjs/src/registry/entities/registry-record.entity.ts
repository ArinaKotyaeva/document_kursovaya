import {
  Column,
  CreateDateColumn,
  Entity,
  PrimaryGeneratedColumn,
  UpdateDateColumn,
} from 'typeorm';

@Entity({ name: 'registry_records' })
export class RegistryRecord {
  @PrimaryGeneratedColumn()
  id!: number;

  @Column({ length: 100 })
  name!: string;

  @Column({ length: 255 })
  email!: string;

  @Column({ type: 'int', nullable: true })
  age!: number | null;

  @Column({ name: 'created_by', nullable: true })
  createdBy!: number | null;

  @CreateDateColumn({ name: 'created_at', type: 'timestamptz' })
  createdAt!: Date;

  @UpdateDateColumn({ name: 'updated_at', type: 'timestamptz' })
  updatedAt!: Date;

  @Column({ name: 'is_deleted', default: false })
  isDeleted!: boolean;
}
