window.onload = function() {
var ctx = document.getElementById('graf1').getContext('2d');
window.michart = new Chart(ctx, {type:'bar',data:{labels:[1],datasets:[{data:[1]}]}});
var ctx2 = document.getElementById('graf2').getContext('2d');
window.michart2 = new Chart(ctx2, {type:'bar',data:{labels:[1],datasets:[{data:[1]}]}});
};
function vergraph(ep,eq){
var xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
if (this.readyState == 4) {
if (this.status == 200) {
var rp=JSON.parse(this.responseText);
window.michart.data.labels=rp.rotulos;
window.michart.data.datasets=[{ borderColor: '#00ff00', backgroundColor: '#00ff00', data: rp.avg, label: rp.tavg},{ borderColor: '#ff7f00', backgroundColor: '#ff7f00', label: rp.tmin, data: rp.min},{ borderColor: '#0000ff', backgroundColor: '#0000ff', label: rp.tmax, data: rp.max}];
window.michart.options={animation: {duration:0}, legend: {position: 'bottom'}, aspectRatio: 1.8,title: { display: true, text: rp.titulo, fontSize:24, fontColor: '#0000ff'},tooltips: {mode: 'index', intersect: false}, scales:{xAxes: [{display: true, scaleLabel:{display: true,labelString: rp.ejex }}],yAxes:[{display:true, ticks:{suggestedMin:0},scaleLabel:{display:true,labelString:rp.unidad}}] } };        
window.michart.update();
window.michart2.data.labels=rp.rotulos;
if (rp.tipo=='s'){
window.michart2.data.datasets=[{ barPercentage: 1, categoryPercentage: 1, borderColor: '#000000', backgroundColor: '#000000', data: rp.u, label: rp.tu},{ barPercentage: 1, categoryPercentage: 1, borderColor: '#ff0000', backgroundColor: '#ff0000', label: rp.tc, data: rp.c},{ barPercentage: 1, categoryPercentage: 1, borderColor: '#ffff00', backgroundColor: '#ffff00', label: rp.tw, data: rp.w},{ barPercentage: 1, categoryPercentage: 1, borderColor: '#00ff00', backgroundColor: '#00ff00', label: rp.to, data: rp.ok}];
window.michart2.options={animation: {duration:0}, legend: {position: 'bottom'}, aspectRatio: 3,title: { display: false },tooltips: {enabled:false},scales:{xAxes: [{stacked: true, display: true, scaleLabel:{display: true,labelString: rp.ejex }}],yAxes:[{stacked:true, display:true, ticks: {min: 0, max: 4, stepSize: 1,callback: function (ep){if (ep==4){return 'OK';} if (ep==3){return 'WA';} if (ep==2){return 'CR';} if (ep==1){return 'UN';} return ' ';}},scaleLabel:{display:true,labelString: rp.stat}}] } };        
}else{
window.michart2.data.datasets=[{ barPercentage: 1, categoryPercentage: 1,borderColor: '#000000', backgroundColor: '#000000', data: rp.u, label: rp.tu},{ barPercentage: 1, categoryPercentage: 1, borderColor: '#ff0000', backgroundColor: '#ff0000', label: rp.tc, data: rp.c},{ barPercentage: 1, categoryPercentage: 1, borderColor: '#00ff00', backgroundColor: '#00ff00', label: rp.to, data: rp.ok}];
window.michart2.options={animation: {duration:0}, legend: {position: 'bottom'}, aspectRatio: 3, title: { display: false },tooltips: {enabled:false},scales:{xAxes: [{stacked: true, display: true, scaleLabel:{display: true,labelString: rp.ejex }}],yAxes:[{stacked:true, display:true, ticks: {min: 0, max: 3, stepSize: 1,callback: function (ep){ if (ep==3){return 'UP';} if (ep==2){return 'DW';} if (ep==1){return 'UN';} return ' ';}},scaleLabel:{display:true,labelString: rp.stat}}] } };        
}
window.michart2.update();
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