import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { DataSource, Repository } from 'typeorm';
import { RegistryService } from '../registry/registry.service';
import { ReportExport } from './entities/report-export.entity';

@Injectable()
export class ReportsService {
  constructor(
    private readonly registryService: RegistryService,
    @InjectRepository(ReportExport)
    private readonly exportsRepo: Repository<ReportExport>,
    private readonly dataSource: DataSource,
  ) {}

  async buildReport(userId: number) {
    const records = await this.registryService.findAll();

    await this.dataSource.query('CALL sp_report_register_export($1, $2, $3)', [
      userId,
      'json',
      records.length,
    ]);

    return {
      title: 'Документ учёта — реестр персональных данных',
      exportedAt: new Date().toISOString(),
      total: records.length,
      records,
    };
  }
}
