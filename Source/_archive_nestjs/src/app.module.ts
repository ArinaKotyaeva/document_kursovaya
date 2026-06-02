import { Module } from '@nestjs/common';
import { ConfigModule, ConfigService } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuthModule } from './auth/auth.module';
import { UsersModule } from './users/users.module';
import { LoggingModule } from './logging/logging.module';
import { RegistryModule } from './registry/registry.module';
import { ReportsModule } from './reports/reports.module';
import { ApiRequestLog } from './logging/entities/api-request-log.entity';
import { AppUser } from './users/entities/app-user.entity';
import { Role } from './users/entities/role.entity';
import { RegistryRecord } from './registry/entities/registry-record.entity';
import { ReportExport } from './reports/entities/report-export.entity';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    TypeOrmModule.forRootAsync({
      inject: [ConfigService],
      useFactory: (config: ConfigService) => ({
        type: 'postgres',
        host: config.get('DB_HOST', 'localhost'),
        port: Number(config.get('DB_PORT', 5432)),
        username: config.get('DB_USERNAME', 'postgres'),
        password: config.get('DB_PASSWORD', 'postgres'),
        database: config.get('DB_DATABASE', 'document_kursovaya'),
        entities: [Role, AppUser, RegistryRecord, ReportExport, ApiRequestLog],
        synchronize: false,
      }),
    }),
    AuthModule,
    UsersModule,
    RegistryModule,
    ReportsModule,
    LoggingModule,
  ],
})
export class AppModule {}
