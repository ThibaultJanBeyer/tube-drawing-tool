import processing.pdf.*;

boolean dpressed = false;

void setup(){
  size(c_width,c_height, PDF, "rohr.pdf");
  frameRate(120);
}

void draw(){
  if(dpressed == true){
    fill(random(255),random(255),random(255));
    ellipse(mouseX,mouseY,c_width/8,c_height/8);
  }
  if(frameResize == true){
    size(c_width,c_height);
    frameResize = false;
  }
}

void keyPressed(){
  if (key=='d' || key=='D') {
    if(dpressed == false){
      dpressed = true;
    } else {
      dpressed = false;
    }
  }
  if (key=='s' || key=='S'){
    save();
  }
}

