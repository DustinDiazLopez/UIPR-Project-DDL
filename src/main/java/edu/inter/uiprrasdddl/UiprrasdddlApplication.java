package edu.inter.uiprrasdddl;

import edu.inter.uiprrasdddl.property.FileStorageProperties;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.context.properties.EnableConfigurationProperties;

@SpringBootApplication
@EnableConfigurationProperties({
		FileStorageProperties.class
})
public class UiprrasdddlApplication {
	public static void main(String[] args) {
		SpringApplication.run(UiprrasdddlApplication.class, args);
	}
}
