#define pi 3.14
#define tao 6.28

#define overbright 0.05

#define armCount 2.0
#define armRot 1.9

#define innerColor vec4(2.0,0.5,0.1,1.0)
#define outerColor vec4(0.8,0.6,1.0,1.0)
#define white vec4(1.0,1.0,1.0,1.0)


float saturate (in float f)
{
  return clamp(f,0.0,1.0);
}

void mainImage( out vec4 fragColor, in vec2 fragCoord )
{
  float time = iTime;
  
vec2 uv = fragCoord.xy / iResolution.xy;
  
  //constant slow rotation
  float cost = cos(-time*0.2);
  float sint = sin(-time*0.2);
  mat2 trm = mat2 (cost,sint,-sint,cost);
  
  //scale 0.0-1.0 uv to -1.0-1.0 p
  vec2 p = uv*2.0 - 1.0;
  //apply slow rotation
  p = p * trm;
  
  //calc distance
  float d = length(p);
  
  //build arm rotation matrix
  float cosr = cos(armRot*sin(armRot*time));
  float sinr = sin(armRot*cos(armRot*time));
  mat2 rm = mat2 (cosr,sinr,-sinr,cosr);
  
  //calc arm rotation based on distance
  p = mix(p,p * rm,d);
  
  //find angle to middle
  float angle = (atan(p.y,p.x)/tao) * 0.5 + 0.5;
  //add the crinkle
  angle += sin(-time*5.0+fract(d*d*d)*10.0)*0.004;
  //calc angle in terms of arm number
  angle *= 2.0 * armCount;
  angle = fract(angle);
  //build arms & wrap the angle around 0.0 & 1.0
  float arms = abs(angle*2.0-1.0);
  //sharpen arms
  arms = pow(arms,10.0*d*d + 5.0);
  //calc radial falloff
  float bulk = 1.0 - saturate(d);
  //create glowy center
  float core = pow(bulk,9.0);
  //calc color
  vec4 color = mix(innerColor,outerColor,d*2.0);
  
fragColor = bulk * arms * color + core + bulk*0.25*mix(color,white,0.5);
  fragColor = (overbright * fragColor);

  fragColor = 1. - fragColor;
}


/* 
// Lines
////////////////////////////////////////////////////////////////////////////////
//
// Playing around with simplex noise and polar-coords with a lightning-themed
// scene.
//
// Copyright 2019 Mirco Müller
//
// Author(s):
//   Mirco "MacSlow" Müller <macslow@gmail.com>
//
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License version 3, as published
// by the Free Software Foundation.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranties of
// MERCHANTABILITY, SATISFACTORY QUALITY, or FITNESS FOR A PARTICULAR
// PURPOSE.  See the GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program.  If not, see <http://www.gnu.org/licenses/>.
//
////////////////////////////////////////////////////////////////////////////////

mat2 r2d (in float degree)
{
	float rad = radians (degree);
	float c = cos (rad);
	float s = sin (rad);
	return mat2 (vec2 (c, s),vec2 (-s, c));
}

// using a slightly adapted implementation of iq's simplex noise from
// https://www.shadertoy.com/view/Msf3WH with hash(), noise() and fbm()
vec2 hash (in vec2 p)
{
	p = vec2 (dot (p, vec2 (127.1, 311.7)),
			  dot (p, vec2 (269.5, 183.3)));

	return -1. + 2.*fract (sin (p)*43758.5453123);
}

float noise (in vec2 p)
{
    const float K1 = .366025404;
    const float K2 = .211324865;

	vec2 i = floor (p + (p.x + p.y)*K1);
	
    vec2 a = p - i + (i.x + i.y)*K2;
    vec2 o = step (a.yx, a.xy);    
    vec2 b = a - o + K2;
	vec2 c = a - 1. + 2.*K2;

    vec3 h = max (.5 - vec3 (dot (a, a), dot (b, b), dot (c, c) ), .0);

	vec3 n = h*h*h*h*vec3 (dot (a, hash (i + .0)),
						   dot (b, hash (i + o)),
						   dot (c, hash (i + 1.)));

    return dot (n, vec3 (70.));
}

float fbm (in vec2 p)
{
	mat2 rot = r2d (27.5);
    float d = noise (p); p *= rot;
    d += .5*noise (p); p *= rot;
    d += .25*noise (p); p *= rot;
    d += .125*noise (p); p *= rot;
    d += .0625*noise (p);
	d /= (1. + .5 + .25 + .125 + .0625);
	return .5 + .5*d;
}

vec2 mapToScreen (in vec2 p, in float scale)
{
    vec2 res = p;
    res = res * 2. - 1.;
    res.x *= iResolution.x / iResolution.y;
    res *= scale;
    
    return res;
}

vec2 cart2polar (in vec2 cart)
{
    float r = length (cart);
    float phi = atan (cart.y, cart.x);
    return vec2 (r, phi); 
}

vec2 polar2cart (in vec2 polar)
{
    float x = polar.x*cos (polar.y);
    float y = polar.x*sin (polar.y);
    return vec2 (x, y); 
}

void mainImage( out vec4 fragColor, in vec2 fragCoord )
{
    vec2 uv = mapToScreen (fragCoord.xy/iResolution.xy, 2.5);

	uv *= r2d (12.*iTime);
    float len = length (uv);
	float thickness = .25;
    float haze = 2.5;

    // distort UVs a bit
    uv = cart2polar (uv);
    uv.y += .2*(.5 + .5*sin(cos (uv.x)*len));
    uv = polar2cart (uv);

    float d1 = abs ((uv.x*haze)*thickness / (uv.x + fbm (uv + 1.25*iTime)));
    float d2 = abs ((uv.y*haze)*thickness / (uv.y + fbm (uv - 1.5*iTime)));
    float d3 = abs ((uv.x*uv.y*haze)*thickness / (uv.x*uv.y + fbm (uv - 2.*iTime)));
    vec3 col = vec3 (.0);
    float size = .075;
	col += d1*size*vec3 (.1, .8, 2.);
	col += d2*size*vec3 (2., .1, .8);
	col += d3*size*vec3 (.8, 2., .1);

    fragColor = vec4 (col, 1.);
}
*/

/*
  // Stars
	float Hash31(in vec3 p) {
		 return fract(937.276 * cos(836.826 * p.x + 263.736 * p.y + 374.723 * p.z + 637.839));
	}

	void mainImage(out vec4 fragColor, in vec2 fragCoord) {
		vec2 uv = (fragCoord - 0.5 * iResolution.xy) / iResolution.y * 4.0;
		float time = iTime * 2.0;
		vec3 color = vec3(0.0);

		for (float i=-3.0; i <= 3.0; i += 1.25) {
			for (float j=-2.0; j <= 2.0; j += 1.25) {
				vec2 p = uv;

				float freq = fract(643.376 * cos(264.863 * i + 136.937)) + 1.0;
				vec2 pos = 5.0 * vec2(i, j) + vec2(sin(freq * (iTime + 10.0 * j) - i), freq * iTime);
				pos.y = mod(pos.y + 15.0, 30.0) - 15.0;
				pos.x *= 0.1 * pos.y + 1.0;
				p -= 0.2 * pos;

				float an = mod(atan(p.y, p.x) + 6.2831 / 3.0, 6.2831 / 6.0) - 6.2831 / 3.0;
				p = vec2(cos(an), sin(an)) * length(p);

				float sec = floor(time);
				float frac = fract(time);
				float flicker = mix(Hash31(vec3(i, j, sec)), Hash31(vec3(i, j, sec + 1.0)), frac);

				float rad = 25.0 + 20.0 * flicker;
				float br = 250.0 * pow(1.0 / max(10.0, rad * (sqrt(abs(p.x)) + sqrt(abs(p.y))) + 0.9), 2.5);
				float rand = fract(847.384 * cos(483.846 * i + 737.487 * j + 264.836));
				if (rand > 0.5) color += mix(vec3(br, 0.4 * br, 0.0), vec3(1.0), br);
				else color += mix(vec3(0.0, 0.0, 0.6 * br), vec3(1.0), br);

				color *= 0.955 + 0.1 * flicker;
			}
		}

		fragColor = vec4(color, 1.0);
	}
*/

/* 
  // Lines
  #define COUNT 20.
#define COL_BLACK vec3(23,32,38) / 255.0 

#define SF 1./min(iResolution.x,iResolution.y)
#define SS(l,s) smoothstep(SF,-SF,l-s)
#define hue(h) clamp( abs( fract(h + vec4(3,2,1,0)/3.) * 6. - 3.) -1. , 0., 1.)

// Original noise code from https://www.shadertoy.com/view/4sc3z2
#define MOD3 vec3(.1031,.11369,.13787)

vec3 hash33(vec3 p3)
{
	p3 = fract(p3 * MOD3);
    p3 += dot(p3, p3.yxz+19.19);
    return -1.0 + 2.0 * fract(vec3((p3.x + p3.y)*p3.z, (p3.x+p3.z)*p3.y, (p3.y+p3.z)*p3.x));
}

float simplex_noise(vec3 p)
{
    const float K1 = 0.333333333;
    const float K2 = 0.166666667;
    
    vec3 i = floor(p + (p.x + p.y + p.z) * K1);
    vec3 d0 = p - (i - (i.x + i.y + i.z) * K2);
        
    vec3 e = step(vec3(0.0), d0 - d0.yzx);
	vec3 i1 = e * (1.0 - e.zxy);
	vec3 i2 = 1.0 - e.zxy * (1.0 - e);
    
    vec3 d1 = d0 - (i1 - 1.0 * K2);
    vec3 d2 = d0 - (i2 - 2.0 * K2);
    vec3 d3 = d0 - (1.0 - 3.0 * K2);
    
    vec4 h = max(0.6 - vec4(dot(d0, d0), dot(d1, d1), dot(d2, d2), dot(d3, d3)), 0.0);
    vec4 n = h * h * h * h * vec4(dot(d0, hash33(i)), dot(d1, hash33(i + i1)), dot(d2, hash33(i + i2)), dot(d3, hash33(i + 1.0)));
    
    return dot(vec4(31.316), n);
}

void mainImage( out vec4 fragColor, in vec2 fragCoord )
{
    
    vec2 uv = fragCoord/iResolution.y;
    
    float m = 0.;
    float t = iTime *.5;
    vec3 col;
    for(float i=COUNT; i>=0.; i-=1.){
        float edge = simplex_noise(vec3(uv * vec2(2., 0.) + vec2(0, t + i*.15), 1.))*.2 + (.5/COUNT)*i + .25;
        float mi = SS(edge, uv.y) - SS(edge + .005, uv.y);        
        m *= SS(edge, uv.y+.015);
        m += mi;        
        
        if(mi > 0.){
        	col = hue(i/COUNT).rgb;
        }        
    }           
    
    col = mix(COL_BLACK, col, m);
    
    fragColor = vec4(col,1.0);
}
*/

/*

// road

void mainImage(out vec4 O, vec2 I)
{
    //Resolution for scaling
    vec2 r = iResolution.xy, o;
    //Clear fragcolor
    O*=0.;
    //Render 50 lightbars
    float jj = fract(-iTime);
    for(float i=0.0; i<25.; i+=.5)
        //Offset coordinates (center of bar)
        o = (I+I-r)/r.y*i + cos(i*vec2(.8,.5)+iTime),
        //Light color
        O += (cos(i+vec4(0,2,4,0))+1.) / max(i*i,5.)*.1 / (i/1e3+
        //Attenuation using distance to line
        length(o-vec2(clamp(o.x,-4.,4.),i+o*sin(i)*.1-4.))/i);
}

*/

/*
// circles

  
  void mainImage(out vec4 O, vec2 I)
{
    //Clear frag color
    O *= 0.;
    //Resolution for scaling
    vec2 r = iResolution.xy;
    //Initialize the iterator and ring distance
    float d = 0.;
    for( float i = 0. ; i < 50.0 ; i += 1.0 )
    {
        d = mod(i-iTime,5e1)+.01;

        O += (cos(i*i+vec4(6,7,8,0))+1.)/(abs(length(I-r*.5+cos(r*i)*r.y/d+d/.4)/r.y*d-.2)+8./r.y)*min(d,1.)/++d/2e1;
    }
}


*/

/*
// eclipse

//<200 chars playlist: https://www.shadertoy.com/playlist/N3lGDN

//Eclipse 1: https://www.shadertoy.com/view/4d2fzw
//Eclipse 2: https://www.shadertoy.com/view/NdlfzX
//Eclipse 3: https://www.shadertoy.com/view/slffRX

void mainImage(out vec4 O, vec2 I)
{
    O = vec4(0,I=1.2-I/iResolution.x/.4,1.-dot(I,I++));
    O = I.y/(.7+abs(O.wwww)*1e2)+.8*max(O.z-(O.w>0.?sqrt(O.w)-.3:.2),0.);
    O.xy *= I*.3; O*=O;
}

*/

