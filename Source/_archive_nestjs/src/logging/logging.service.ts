import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { ApiRequestLog } from './entities/api-request-log.entity';

@Injectable()
export class LoggingService {
  constructor(
    @InjectRepository(ApiRequestLog)
    private readonly logsRepo: Repository<ApiRequestLog>,
  ) {}

  async writeLog(payload: {
    userId: number | null;
    method: string;
    path: string;
    statusCode: number;
    durationMs: number;
  }) {
    const log = this.logsRepo.create(payload);
    await this.logsRepo.save(log);
  }
}
