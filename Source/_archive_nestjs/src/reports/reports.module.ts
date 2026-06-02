import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { RegistryModule } from '../registry/registry.module';
import { ReportExport } from './entities/report-export.entity';
import { ReportsController } from './reports.controller';
import { ReportsService } from './reports.service';

@Module({
  imports: [TypeOrmModule.forFeature([ReportExport]), RegistryModule],
  controllers: [ReportsController],
  providers: [ReportsService],
})
export class ReportsModule {}
