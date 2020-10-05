//package edu.inter.uiprrasdddl.config;
//
//import org.springframework.beans.factory.annotation.Autowired;
//import org.springframework.context.annotation.*;
//import org.springframework.core.env.Environment;
//import org.springframework.orm.hibernate5.HibernateTransactionManager;
//import org.springframework.orm.hibernate5.LocalSessionFactoryBean;
//import org.springframework.transaction.annotation.EnableTransactionManagement;
//
//import static org.hibernate.cfg.Environment.*;
//
//import java.util.Properties;
//
//@Configuration
//@PropertySource("classpath:application.properties")
//@EnableTransactionManagement
//@ComponentScans(value = {
//        @ComponentScan("edu.inter.uiprrasdddl.repository"),
//        @ComponentScan("edu.inter.uiprrasdddl.service"),
//})
//public class AppConfig {
//
//    @Autowired
//    private Environment env;
//
//    @Bean
//    public LocalSessionFactoryBean getSessionFactory() {
//        LocalSessionFactoryBean factoryBean = new LocalSessionFactoryBean();
//        Properties properties = new Properties();
//
//        properties.put(DRIVER, env.getProperty("spring.datasource.driver-class-name"));
//        properties.put(URL, env.getProperty("spring.datasource.url"));
//        properties.put(USER, env.getProperty("spring.datasource.username"));
//        properties.put(PASS, env.getProperty("spring.datasource.password"));
//
//        properties.put(SHOW_SQL, env.getProperty("hibernate.show_sql"));
//        properties.put(HBM2DDL_AUTO, env.getProperty("hibernate.hbm2ddl.auto"));
//
////        properties.put(C3P0_MIN_SIZE, env.getProperty("hibernate.c3p0.min_size"));
////        properties.put(C3P0_MAX_SIZE, env.getProperty("hibernate.c3p0.max_size"));
////        properties.put(C3P0_ACQUIRE_INCREMENT, env.getProperty("hibernate.c3p0.acquire_increment"));
////        properties.put(C3P0_TIMEOUT, env.getProperty("hibernate.c3p0.timeout"));
////        properties.put(C3P0_MAX_STATEMENTS, env.getProperty("hibernate.c3p0.max_statements"));
////        properties.put("hibernate.c3p0.validate", true);
////        properties.put("hibernate.c3p0.preferredTestQuery", "select 1");
//
//        factoryBean.setHibernateProperties(properties);
//        factoryBean.setPackagesToScan("edu.inter.uiprrasdddl.model");
//
//        return factoryBean;
//    }
//
//    @Bean
//    public HibernateTransactionManager getTransactionManager() {
//        HibernateTransactionManager transactionManager = new HibernateTransactionManager();
//        transactionManager.setSessionFactory(getSessionFactory().getObject());
//        return transactionManager;
//    }
//}
