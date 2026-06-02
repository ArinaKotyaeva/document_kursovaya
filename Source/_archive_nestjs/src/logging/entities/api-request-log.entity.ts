import {
  Column,
  CreateDateColumn,
  Entity,
  PrimaryGeneratedColumn,
} from 'typeorm';

@Entity({ name: 'api_request_logs' })
export class ApiRequestLog {
  @PrimaryGeneratedColumn({ type: 'bigint' })
  id!: string;

  @Column({ name: 'user_id', nullable: true })
  userId!: number | null;

  @Column({ length: 10 })
  method!: string;

  @Column()
  path!: string;

  @Column({ name: 'status_code' })
  statusCode!: number;

  @Column({ name: 'duration_ms', default: 0 })
  durationMs!: number;

  @CreateDateColumn({ name: 'created_at', type: 'timestamptz' })
  createdAt!: Date;
}
