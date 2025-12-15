-- CreateTable
CREATE TABLE "CareerOption" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "code" INTEGER NOT NULL,
    "career" TEXT NOT NULL,
    "shift" TEXT NOT NULL,
    "level" TEXT NOT NULL,
    "area" TEXT NOT NULL,
    "description" TEXT NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL
);

-- CreateIndex
CREATE UNIQUE INDEX "CareerOption_code_key" ON "CareerOption"("code");

-- CreateIndex
CREATE INDEX "CareerOption_career_idx" ON "CareerOption"("career");

-- CreateIndex
CREATE INDEX "CareerOption_shift_idx" ON "CareerOption"("shift");

-- CreateIndex
CREATE INDEX "CareerOption_level_idx" ON "CareerOption"("level");

-- CreateIndex
CREATE INDEX "CareerOption_area_idx" ON "CareerOption"("area");
