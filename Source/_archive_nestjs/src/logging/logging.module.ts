import { Module } from '@nestjs/common';
import { APP_INTERCEPTOR } from '@nestjs/core';
import { TypeOrmModule } from '@nestjs/typeorm';
import { ApiRequestLog } from './entities/api-request-log.entity';
import { LoggingInterceptor } from './logging.interceptor';
import { LoggingService } from './logging.service';

@Module({
  imports: [TypeOrmModule.forFeature([ApiRequestLog])],
  providers: [
    LoggingService,
    {
      provide: APP_INTERCEPTOR,
      useClass: LoggingInterceptor,
    },
  ],
  exports: [LoggingService],
})
export class LoggingModule {}
