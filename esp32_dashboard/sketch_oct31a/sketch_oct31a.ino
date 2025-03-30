#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "iPhone"; 
const char* password = "87654321"; 

const int trigPin = 18;
const int echoPin = 5;
const int buzzerPin = 23; 
const int motorPin = 22; 

void setup() {
    Serial.begin(115200);
    pinMode(trigPin, OUTPUT);
    pinMode(echoPin, INPUT);
    pinMode(buzzerPin, OUTPUT);
    pinMode(motorPin, OUTPUT);
    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
    }
    Serial.println("Connected to WiFi");
}

void loop() {
    long duration, distance;
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    duration = pulseIn(echoPin, HIGH);
    distance = (duration * 0.034) / 2;

    if (distance > 0 && distance < 20) {
        // Activate buzzer and motor
        digitalWrite(buzzerPin, HIGH);
        digitalWrite(motorPin, HIGH);   
        delay(100); // Activate for 1 second
      }else{
            digitalWrite(buzzerPin, LOW);   // Turn off buzzer
            digitalWrite(motorPin, LOW);    // Turn off vibration motor
            }
        
      if (distance < 30){   
          Serial.print("Distance: ");
          Serial.print(distance);
          Serial.println(" cm");
      
          // Send data to server
           sendData(distance);
      }
    
    delay(100);
}

void sendData(long distance) {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        http.begin("http://172.20.10.14/esp32_dashboard/save_data.php"); // Use your actual local IP for testing
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");
        String httpRequestData = "distance=" + String(distance);
        int httpResponseCode = http.POST(httpRequestData);

        if (httpResponseCode > 0) {
            Serial.print("Data sent to server, response code: ");
            Serial.println(httpResponseCode);
        } else {
            Serial.print("Error sending data: ");
            Serial.println(http.errorToString(httpResponseCode).c_str());
        }
        http.end();
    }
}
