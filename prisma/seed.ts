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

// ============================================================
// 3. EXECUTION LOGIC
// ============================================================

async function main() {
  console.log(`Start seeding ...`);

  // 1. Clean up database (Order matters due to foreign keys)
  // We delete Messages first, then Chats, then Users, then Roles
  await prisma.message.deleteMany();
  await prisma.chat.deleteMany();
  await prisma.user.deleteMany();
  await prisma.userRole.deleteMany();

  // 2. Create Roles
  console.log("Seeding Roles...");
  await prisma.userRole.createMany({ data: userRoleData });

  // 3. Create Main User
  // We use .create() here instead of createMany so we can immediately get the returned ID
  console.log("Seeding User...");
  const user = await prisma.user.create({
    data: userData,
  });

  // 4. Create 25 Dummy Chats with Messages
  console.log("Seeding 25 Chats...");

  // We iterate through our topics and create a chat + messages for each
  for (const [index, topic] of chatTopics.entries()) {
    await prisma.chat.create({
      data: {
        userId: user.id,
        title: topic,
        // We can create related messages in the same transaction
        messages: {
          create: [
            {
              role: MessageRole.USER,
              content: `How do I handle ${topic} in my current project?`,
              // Make dates slightly different to simulate history
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
