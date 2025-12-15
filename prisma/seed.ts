import { MessageRole, PrismaClient } from "@prisma/client";
import { PrismaBetterSqlite3 } from "@prisma/adapter-better-sqlite3";

const adapter = new PrismaBetterSqlite3({
  url: process.env.DATABASE_URL!,
});
const prisma = new PrismaClient({ adapter });

const userRoleData = [
  { id: 1, name: "Admin" },
  { id: 2, name: "User" },
];

const userData = {
  name: "Juan",
  email: "j@gmail.com",
  password: "$2b$10$mgjotYzIXwrK1MCWmu4tgeUVnLcb.qzvqwxOq4FXEL8k2obwXivDi", // bcrypt: 1234
  roleId: 1,
};

// A list of realistic tech topics to populate the sidebar
const chatTopics = [
  "React Component Patterns",
  "Next.js Routing Help",
  "Tailwind CSS Layouts",
  "Prisma Schema Design",
  "Python Data Analysis",
  "Rust Memory Safety",
  "Docker Compose Setup",
  "AWS Lambda Functions",
  "GraphQL Resolvers",
  "System Architecture Review",
  "Debugging 500 Error",
  "Centering a Div",
  "SQL Query Optimization",
  "Git Merge Conflicts",
  "Jest Unit Testing",
  "E-commerce Database",
  "Auth.js Integration",
  "Stripe Payment Flow",
  "Mobile Responsiveness",
  "WebSockets with Node.js",
  "CI/CD Pipelines",
  "Redis Caching Strategies",
  "ElasticSearch Setup",
  "Figma to Code",
  "Refactoring Legacy Code",
];

const careerOptionsData = [
  {
    code: 1,
    career: "Ingeniería Civil",
    shift: "Diurno",
    level: "Ingeniería",
    area: "Ingeniería y Construcción",
    description:
      "Planifica, diseña y supervisa obras de infraestructura (edificaciones, carreteras, puentes, sistemas hidráulicos) con enfoque en seguridad, calidad y costos.",
  },
  {
    code: 2,
    career: "Ingeniería Civil",
    shift: "Nocturno",
    level: "Ingeniería",
    area: "Ingeniería y Construcción",
    description:
      "Planifica, diseña y supervisa obras de infraestructura (edificaciones, carreteras, puentes, sistemas hidráulicos) con enfoque en seguridad, calidad y costos.",
  },
  {
    code: 3,
    career: "Ingeniería de Sistemas",
    shift: "Vespertino",
    level: "Ingeniería",
    area: "Tecnología",
    description:
      "Desarrolla y administra soluciones tecnológicas: software, bases de datos, redes y sistemas de información para mejorar procesos y servicios.",
  },
  {
    code: 4,
    career: "Licenciatura en Administración de Empresas",
    shift: "Dominical",
    level: "Licenciatura",
    area: "Ciencias Económicas y Administrativas",
    description:
      "Gestiona organizaciones: planificación, finanzas, talento humano, operaciones y emprendimiento, orientado a la toma de decisiones.",
  },
  {
    code: 5,
    career: "Licenciatura en Administración de Empresas",
    shift: "Vespertino",
    level: "Licenciatura",
    area: "Ciencias Económicas y Administrativas",
    description:
      "Gestiona organizaciones: planificación, finanzas, talento humano, operaciones y emprendimiento, orientado a la toma de decisiones.",
  },
  {
    code: 6,
    career: "Licenciatura en Administración de Empresas",
    shift: "Nocturno",
    level: "Licenciatura",
    area: "Ciencias Económicas y Administrativas",
    description:
      "Gestiona organizaciones: planificación, finanzas, talento humano, operaciones y emprendimiento, orientado a la toma de decisiones.",
  },
  {
    code: 7,
    career: "Licenciatura en Biología Marina",
    shift: "Vespertino",
    level: "Licenciatura",
    area: "Ciencias Naturales",
    description:
      "Estudia ecosistemas marinos y costeros, biodiversidad y manejo sostenible de recursos marinos, con trabajo de campo y laboratorio.",
  },
  {
    code: 8,
    career:
      "Licenciatura en Ciencias de la Educación con mención en Ciencias Naturales",
    shift: "Diurno",
    level: "Licenciatura",
    area: "Educación",
    description:
      "Forma docentes para enseñar ciencias naturales, aplicando estrategias pedagógicas, didácticas y evaluación del aprendizaje.",
  },
  {
    code: 9,
    career:
      "Licenciatura en Ciencias de la Educación con mención en Ciencias Sociales",
    shift: "Sabatino",
    level: "Licenciatura",
    area: "Educación",
    description:
      "Forma docentes para enseñar historia, geografía y ciencias sociales, promoviendo pensamiento crítico y ciudadanía.",
  },
  {
    code: 10,
    career:
      "Licenciatura en Ciencias de la Educación con mención en Lengua y Literatura Hispánicas",
    shift: "Sabatino",
    level: "Licenciatura",
    area: "Educación",
    description:
      "Forma docentes para enseñanza de lengua y literatura, fortaleciendo comunicación, lectura crítica y producción textual.",
  },
  {
    code: 11,
    career: "Licenciatura en Contaduría Pública",
    shift: "Sabatino",
    level: "Licenciatura",
    area: "Ciencias Económicas y Administrativas",
    description:
      "Registra y analiza información financiera, contabilidad, auditoría y tributación para apoyar la transparencia y decisiones económicas.",
  },
  {
    code: 12,
    career: "Licenciatura en Contaduría Pública",
    shift: "Dominical",
    level: "Licenciatura",
    area: "Ciencias Económicas y Administrativas",
    description:
      "Registra y analiza información financiera, contabilidad, auditoría y tributación para apoyar la transparencia y decisiones económicas.",
  },
  {
    code: 13,
    career: "Licenciatura en Derecho",
    shift: "Dominical",
    level: "Licenciatura",
    area: "Ciencias Jurídicas",
    description:
      "Estudia normas jurídicas y procedimientos, con formación para asesoría legal, gestión de casos y promoción de justicia.",
  },
  {
    code: 14,
    career: "Licenciatura en Derecho",
    shift: "Sabatino",
    level: "Licenciatura",
    area: "Ciencias Jurídicas",
    description:
      "Estudia normas jurídicas y procedimientos, con formación para asesoría legal, gestión de casos y promoción de justicia.",
  },
  {
    code: 15,
    career: "Licenciatura en Educación Física y Deportes",
    shift: "Diurno",
    level: "Licenciatura",
    area: "Educación",
    description:
      "Promueve actividad física, entrenamiento deportivo y salud; planifica clases, programas y eventos deportivos.",
  },
  {
    code: 16,
    career: "Licenciatura en Enfermería",
    shift: "Diurno",
    level: "Licenciatura",
    area: "Salud",
    description:
      "Brinda cuidados de enfermería a personas y comunidades, promoción de la salud, prevención y atención clínica con ética profesional.",
  },
  {
    code: 17,
    career: "Licenciatura en Turismo Sostenible",
    shift: "Matutino",
    level: "Licenciatura",
    area: "Turismo",
    description:
      "Gestiona destinos y servicios turísticos con enfoque sostenible, cultura local y conservación ambiental.",
  },
  {
    code: 18,
    career: "Medicina General",
    shift: "Diurno",
    level: "Medicina",
    area: "Salud",
    description:
      "Forma profesionales para diagnóstico, prevención y tratamiento de enfermedades, con base científica y atención centrada en la persona.",
  },
  {
    code: 19,
    career: "Técnico Superior en Diseño Gráfico",
    shift: "Sabatino",
    level: "Técnico Superior",
    area: "Diseño y Comunicación Visual",
    description:
      "Crea piezas visuales para medios impresos y digitales: branding, composición, tipografía y herramientas de diseño.",
  },
  {
    code: 20,
    career: "Técnico Superior en Enfermería",
    shift: "Diurno",
    level: "Técnico Superior",
    area: "Salud",
    description:
      "Apoya la atención de salud con cuidados básicos, procedimientos y promoción de la salud bajo supervisión profesional.",
  },
  {
    code: 21,
    career: "Técnico Superior en Redes",
    shift: "Dominical",
    level: "Técnico Superior",
    area: "Tecnología",
    description:
      "Instala, configura y mantiene redes y servicios básicos (cableado, equipos, seguridad y soporte).",
  },
  {
    code: 22,
    career: "Técnico Superior en Topografía",
    shift: "Dominical",
    level: "Técnico Superior",
    area: "Ingeniería y Construcción",
    description:
      "Realiza mediciones y levantamientos topográficos, manejo de instrumentos y apoyo a proyectos de construcción y ordenamiento territorial.",
  },
];

// ============================================================
// EXECUTION LOGIC
// ============================================================

async function main() {
  console.log(`Start seeding ...`);

  // 1. Clean up database (Order matters due to foreign keys)
  await prisma.message.deleteMany();
  await prisma.chat.deleteMany();

  // New table cleanup (no FKs; safe here)
  await prisma.careerOption.deleteMany();

  await prisma.user.deleteMany();
  await prisma.userRole.deleteMany();

  // 2. Create Roles
  console.log("Seeding Roles...");
  await prisma.userRole.createMany({ data: userRoleData });

  // 3. Create Main User
  console.log("Seeding User...");
  const user = await prisma.user.create({
    data: userData,
  });

  // 4. Seed Career Options (new data)
  console.log("Seeding Career Options...");
  await prisma.careerOption.createMany({
    data: careerOptionsData,
  });

  // 5. Create 25 Dummy Chats with Messages
  console.log("Seeding 25 Chats...");
  for (const [index, topic] of chatTopics.entries()) {
    await prisma.chat.create({
      data: {
        userId: user.id,
        title: topic,
        messages: {
          create: [
            {
              role: MessageRole.USER,
              content: `How do I handle ${topic} in my current project?`,
              createdAt: new Date(Date.now() - 1000 * 60 * 60 * (index + 1)),
            },
            {
              role: MessageRole.ASSISTANT,
              content: `Here is a brief guide on how to handle **${topic}**. \n\n1. Check the documentation.\n2. Ensure your environment is set up.\n3. let me know if you need code snippets!`,
              createdAt: new Date(Date.now() - 1000 * 60 * 60 * index + 5000),
            },
          ],
        },
      },
    });
  }

  console.log(
    `Seeding finished. Created user ${user.email} with ${chatTopics.length} chats.`
  );
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
