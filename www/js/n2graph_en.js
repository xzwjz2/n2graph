window.onload = function() {
var ctx = document.getElementById('graf1').getContext('2d');
window.michart = new Chart(ctx, {type:'bar',data:{labels:[1],datasets:[{data:[1]}]}});
};
function vergraph(ep,eq){
var xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4) {
if (this.status == 200) {
var rp=JSON.parse(this.responseText);
window.michart.data.labels=rp.rotulos;
window.michart.data.datasets=[{ borderColor: '#00ff00', backgroundColor: '#00ff00', data: rp.avg, label: 'Average'},{ borderColor: '#ff7f00', backgroundColor: '#ff7f00', label: 'Minimum', data: rp.min},{ borderColor: '#0000ff', backgroundColor: '#0000ff', label: 'Maximum', data: rp.max}];
window.michart.options={animation: {duration:0}, legend: {position: 'bottom'}, aspectRatio: 1.8,title: { display: true, text: rp.titulo, fontSize:24, fontColor: '#0000ff'},tooltips: {mode: 'index', intersect: false}, scales:{xAxes: [{display: true, scaleLabel:{display: true,labelString: rp.ejex }}],yAxes:[{display:true, ticks:{suggestedMin:0},scaleLabel:{display:true,labelString:'Value'}}] } };        
window.michart.update();
document.getElementById("psel").style.display='block';
} else if (this.status== 401){
document.getElementById("pselcon").innerHTML = this.responseText;
document.getElementById("psel").style.display='block';
}}};
xhttp.open("POST", "n2g_data.php", true);
xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
var z="num="+ep+"&mod="+document.getElementById('modulo').value+"&ftoken="+document.getElementById('ftoken').value;
if (eq !== undefined){z=z+"&"+eq+"=1";}
xhttp.send(z);
}
