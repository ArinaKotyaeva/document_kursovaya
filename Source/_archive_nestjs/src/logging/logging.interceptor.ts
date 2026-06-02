import {
  CallHandler,
  ExecutionContext,
  Injectable,
  NestInterceptor,
} from '@nestjs/common';
import { Observable, tap } from 'rxjs';
import { JwtPayload } from '../auth/jwt.strategy';
import { LoggingService } from './logging.service';

@Injectable()
export class LoggingInterceptor implements NestInterceptor {
  constructor(private readonly loggingService: LoggingService) {}

  intercept(context: ExecutionContext, next: CallHandler): Observable<unknown> {
    const http = context.switchToHttp();
    const request = http.getRequest<{
      method: string;
      url: string;
      user?: JwtPayload;
    }>();
    const response = http.getResponse<{ statusCode: number }>();
    const started = Date.now();

    return next.handle().pipe(
      tap({
        next: async () => {
          await this.loggingService.writeLog({
            userId: request.user?.sub ?? null,
            method: request.method,
            path: request.url,
            statusCode: response.statusCode,
            durationMs: Date.now() - started,
          });
        },
        error: async () => {
          await this.loggingService.writeLog({
            userId: request.user?.sub ?? null,
            method: request.method,
            path: request.url,
            statusCode: response.statusCode || 500,
            durationMs: Date.now() - started,
          });
        },
      }),
    );
  }
}
