import { Injectable, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { InjectRepository } from '@nestjs/typeorm';
import * as bcrypt from 'bcrypt';
import { Repository } from 'typeorm';
import { AppUser } from '../users/entities/app-user.entity';
import { LoginDto } from './dto/login.dto';

@Injectable()
export class AuthService {
  constructor(
    @InjectRepository(AppUser)
    private readonly usersRepo: Repository<AppUser>,
    private readonly jwtService: JwtService,
  ) {}

  async login(dto: LoginDto) {
    const user = await this.usersRepo.findOne({
      where: { email: dto.email, isActive: true },
    });

    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }

    const valid = await bcrypt.compare(dto.password, user.passwordHash);
    if (!valid) {
      const pgcryptoValid = await this.usersRepo.query(
        'SELECT crypt($1, $2) = $2 AS ok',
        [dto.password, user.passwordHash],
      );
      if (!pgcryptoValid[0]?.ok) {
        throw new UnauthorizedException('Invalid credentials');
      }
    }

    const payload = {
      sub: user.id,
      email: user.email,
      role: user.role.code,
      fullName: user.fullName,
    };

    return {
      accessToken: await this.jwtService.signAsync(payload),
      user: {
        id: user.id,
        email: user.email,
        fullName: user.fullName,
        role: user.role.code,
      },
    };
  }
}
