function DocumentModel(){
    
    //predefine a 1000 sliders, that oughta do it
    sliderValueArray = [];
    for (i = 0; i < 1000; i++){
        sliderValueArray[i] = 20;
    }
    
//  this.service_url = "http://hmifact.ewi.utwente.nl:24681"
    this.service_url = "http://bookstore.ewi.utwente.nl:24681"
    
//pre load all possible metadatas (0 to 200)?
//    this.element = ko.observableArray(metadataArr);
        
    this.text = ko.observable("");
    this.summary = ko.observable();
    this.tags = ko.observable("");
    this.entities = ko.observable("");

    // intermediate results from automatic processes
    this.automaticsummary = ko.observable("");
    this.automatickeywords = ko.observable([]);
    this.automaticentities = ko.observable();

    this.slider_values = ko.observableArray(sliderValueArray);

    this.updateSlidervalues = function(){
    }

/*    // actions
    this.updatesummary = function() {
        // steps:

        // 0) get the summary sentences
        sentences = this.automaticsummary().slice();

        // 1) sort by descending score, 
        sentences.sort(function(a,b){return b.score - a.score;});
        // for (var i = 0; i < sentences.length && i < 5; i++) {
        //   console.log(sentences[i].score + ": " + sentences[i].sentence);
        // }

        // 2) select percentage of sentences (round up) based on this.summarylength, 
        var slidervalue = $('#summaryslider').slider('getValue');
        numsentences = Math.ceil(sentences.length * slidervalue / 100);
        sentences = sentences.splice(0, numsentences);

        // 3) sort by idx
        sentences.sort(function(a,b){return a.idx - b.idx;});

        // 4) join sentences to create summary
        s = "";
        for (var i = 0; i < sentences.length; i++) {
            s += sentences[i].sentence + " ";
        }
        this.summary(s.trim());

    }.bind(this);

    this.createsummary = function() {
        console.log("why is this executing?");
        $.ajax({
            url: this.service_url + "/summary",
            type: 'POST',
            dataType: 'JSON',
            data: {
                text: this.text()
            }
        }).done(function(data) {
            if (data.status == 'OK') {
                this.automaticsummary(data.annotation.summary);
                this.updatesummary();
            }
        }.bind(this)
        ).fail(function(data) {
            alert('Error creating summary')
        });
    }.bind(this);

    this.detectentities = function() {
    $.ajax({
        url: this.service_url + "/ner",
        type: 'POST',
        dataType: 'JSON',
        data: {
            text: this.text()
        }
    }).done(function(data) {
        if (data.status == 'OK') {
            var a = [];
            k = data.annotation.entities;
            for (var i = 0; i < k.length; i++) {
                a.push(
            {entity: k[i], checked: ko.observable(true)}
          );
        }
        this.automaticentities(a);
        } else {
            alert('Could not determine entities: ' + data.message);
        }
    }.bind(this)
    ).fail(function(data) {
        alert('Error detecting entities')
    });
    }.bind(this);

    this.addentityselection = function() {
        var t = this.entities().trim();
        if (t.length > 0) {
            t += "\n"
        }
        this.automaticentities().filter(function(a) {
            if (a.checked()) {
                t += a.entity + "\n";
            }
            return false;
        });
        t = t.trim();
        this.entities(t);
    }.bind(this);
*/
    this.detecttags = function() {
    $.ajax({
        url: this.service_url + "/keywords",
        type: 'POST',
        dataType: 'JSON',
        data: {
            text: this.text()
        }
    }).done(function(data) {
        if (data.status == 'OK') {
            var a = [];
            k = data.annotation.keywords;
            for (var i = 0; i < k.length; i++) {
                a.push(
                    {keyword: k[i].keyword, score: k[i].score, checked: ko.observable(k[i].score > 10)}
                );
            }
            this.automatickeywords(a);
        }
    }.bind(this)
        ).fail(function(data) {
            alert('Error detecting keywords')
        });
    }.bind(this);

    this.addselection = function() {
        var t = this.keywords().trim();
        if (t.length > 0) {
            t += "\n"
        }
        this.automatickeywords().filter(function(a) {
            if (a.checked()) {
                t += a.keyword + "\n";
            }
            return false;
        });
        t = t.trim();
        this.keywords(t);
    }.bind(this);
};

/*
define a new language named "custom"
*/
jQuery.dateRangePickerLanguages['custom'] = 
{
	'selected': 'Geselecteerd:',
	'days': 'Dagen',
	'apply': 'Sluiten',
	'week-1' : 'Ma',
	'week-2' : 'Di',
	'week-3' : 'Wo',
	'week-4' : 'Do',
	'week-5' : 'Vr',
	'week-6' : 'Za',
	'week-7' : 'Zo',
	'month-name': ['january','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'],
	'shortcuts' : 'Selecties',
	'fragments' : 'Fragmenten',
	'past': 'Afgelopen',
	'7days' : '7dagen',
	'14days' : '14dagen',
	'30days' : '30dagen',
	'previous' : 'Vorige',
	'prev-week' : 'Week',
	'prev-month' : 'Maand',
	'prev-quarter' : 'Kwartaal',
	'prev-year' : 'Jaar',
	'year': 'Jaar',
	'century': 'Eeuw',
	'less-than' : 'Datum bereik moet langer dan %d dagen',
	'more-than' : 'Datum bereik moet korter dan %d dagen',
	'default-more' : 'Selecteer een datum bereik langer dan %d dagen',
	'default-less' : 'Selecteer een datum bereik korter dan %d dagen',
	'default-range' : 'Selecteer een datum bereik tussen %d en %d dagen',
	'default-default': 'Dit is een aangepaste taal',
	'firstQuarter': 'Eerste kwart',
	'secondQuarter': 'Tweede kwart',
	'thirdQuarter': 'Derde kwart',
	'fourthQuarter': 'Vierde kwart',
	'beginning': 'Begin van',
	'end': 'Eind van',
	'middle': 'Halverwege',
	'summer': 'Zomer van',
	'winter': 'Winter van',
	'spring': 'Lente van',
	'fall': 'Herfst van'
};