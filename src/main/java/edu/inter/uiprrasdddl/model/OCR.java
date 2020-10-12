package edu.inter.uiprrasdddl.model;

import java.util.UUID;

public class OCR {
    private final UUID id;
    private final String text;

    public OCR(String text) {
        this.id = UUID.randomUUID();
        this.text = text;
    }

    public UUID getId() {
        return id;
    }

    public String getText() {
        return text;
    }
}
