import $ from 'jquery';

/**
 * English stop words courtesy of MySQL
 */
const stopWords = [
    "a's","able","about","above","according","accordingly","across","actually","after","afterwards","again",
    "against","ain't","all","allow","allows","almost","alone","along","already","also","although","always",
    "am","among","amongst","an","and","another","any","anybody","anyhow","anyone","anything","anyway","anyways",
    "anywhere","apart","appear","appreciate","appropriate","are","aren't","around","as","aside","ask","asking",
    "associated","at","available","away","awfully","be","became","because","become","becomes","becoming","been",
    "before","beforehand","behind","being","believe","below","beside","besides","best","better","between","beyond",
    "both","brief","but","by","c'mon","c's","came","can","can't","cannot","cant","cause","causes","certain",
    "certainly","changes","clearly","co","com","come","comes","concerning","consequently","consider","considering",
    "contain","containing","contains","corresponding","could","couldn't","course","currently","definitely",
    "described","despite","did","didn't","different","do","does","doesn't","doing","don't","done","down",
    "downwards","during","each","edu","eg","eight","either","else","elsewhere","enough","entirely","especially",
    "et","etc","even","ever","every","everybody","everyone","everything","everywhere","ex","exactly","example",
    "except","far","few","fifth","first","five","followed","following","follows","for","former","formerly","forth",
    "four","from","further","furthermore","get","gets","getting","given","gives","go","goes","going","gone","got",
    "gotten","greetings","had","hadn't","happens","hardly","has","hasn't","have","haven't","having","he","he's",
    "hello","help","hence","her","here","here's","hereafter","hereby","herein","hereupon","hers","herself","hi",
    "him","himself","his","hither","hopefully","how","howbeit","however","i'd","i'll","i'm","i've","ie","if",
    "ignored","immediate","in","inasmuch","inc","indeed","indicate","indicated","indicates","inner","insofar",
    "instead","into","inward","is","isn't","it","it'd","it'll","it's","its","itself","just","keep","keeps",
    "kept","know","knows","known","last","lately","later","latter","latterly","least","less","lest","let","let's",
    "like","liked","likely","little","look","looking","looks","ltd","mainly","many","may","maybe","me","mean",
    "meanwhile","merely","might","more","moreover","most","mostly","much","must","my","myself","name","namely",
    "nd","near","nearly","necessary","need","needs","neither","never","nevertheless","new","next","nine","no",
    "nobody","non","none","noone","nor","normally","not","nothing","novel","now","nowhere","obviously","of","off",
    "often","oh","ok","okay","old","on","once","one","ones","only","onto","or","other","others","otherwise",
    "ought","our","ours","ourselves","out","outside","over","overall","own","particular","particularly","per",
    "perhaps","placed","please","plus","possible","presumably","probably","provides","que","quite","qv","rather",
    "rd","re","really","reasonably","regarding","regardless","regards","relatively","respectively","right","said",
    "same","saw","say","saying","says","second","secondly","see","seeing","seem","seemed","seeming","seems","seen",
    "self","selves","sensible","sent","serious","seriously","seven","several","shall","she","should","shouldn't",
    "since","six","so","some","somebody","somehow","someone","something","sometime","sometimes","somewhat",
    "somewhere","soon","sorry","specified","specify","specifying","still","sub","such","sup","sure","t's","take",
    "taken","tell","tends","th","than","thank","thanks","thanx","that","that's","thats","the","their","theirs",
    "them","themselves","then","thence","there","there's","thereafter","thereby","therefore","therein","theres",
    "thereupon","these","they","they'd","they'll","they're","they've","think","third","this","thorough",
    "thoroughly","those","though","three","through","throughout","thru","thus","to","together","too","took",
    "toward","towards","tried","tries","truly","try","trying","twice","two","un","under","unfortunately","unless",
    "unlikely","until","unto","up","upon","us","use","used","useful","uses","using","usually","value","various",
    "very","via","viz","vs","want","wants","was","wasn't","way","we","we'd","we'll","we're","we've","welcome",
    "well","went","were","weren't","what","what's","whatever","when","whence","whenever","where","where's",
    "whereafter","whereas","whereby","wherein","whereupon","wherever","whether","which","while","whither","who",
    "who's","whoever","whole","whom","whose","why","will","willing","wish","with","within","without","won't",
    "wonder","would","would","wouldn't","yes","yet","you","you'd","you'll","you're","you've","your","yours",
    "yourself","yourselves","zero"
  ];

export default class WordHighlighter {

  highlight(node, words, excludeStopwords, onlyFirst) {
    let i;
    let w;
    let text;

    const useWords = [];
    const addedNodes = [];

    // We need the longest words to process first or they'll be passed up in favour of shorter guys
    words.sort((a, b) => a.length > b.length ? -1 : 1);

    if (excludeStopwords) {
      words = words.filter(word => stopWords.indexOf(word) === -1);
    }
    if (!words.length) {
      return [];
    }

    // Build a list of words we know are actually in the text
    text = $(node).text().toLowerCase();
    for (i = 0; i < words.length; i++) {
      w = words[i].toLowerCase();
      if (!w || !w.length) {
        continue;
      }
      if (text.indexOf(w) !== -1) {
        useWords.push(w);
      }
    }

    if (!useWords.length) {
      return [];
    }

    this._do(node, useWords, words, addedNodes, onlyFirst, {}, {});

    return addedNodes;
  }

  _do(node, words, originalWords, addedNodes, onlyFirst, _doneWords, _regexCache) {
    let i;
    let findRegex;
    let match;
    let pos;
    let spannode;
    let middlebit;
    let endbit;
    let middleclone;
    let children;

    const procNode = [node];

    while (node = procNode.pop()) {
      if (node.nodeType === 3) {
        for (i = 0; i < words.length; i++) {
          if (onlyFirst && _doneWords[i]) continue;

          if (_regexCache[words[i]]) {
            findRegex = _regexCache[words[i]];
          } else {
            findRegex = new RegExp("\\b" + words[i].replace(/([.?*+^$[\]\\(){}-])/g, "\\$1") + "\\b");
            _regexCache[words[i]] = findRegex;
          }

          match = node.data.toLowerCase().match(findRegex);
          pos = match ? match.index : -1;
          if (pos >= 0 && !$(node.parentNode).hasClass('glossary-link') && !$(node.parentNode).closest('.glossary-link')[0]) {
            _doneWords[i] = true;

            spannode = document.createElement('span');
            spannode.className = 'glossary-link';
            spannode.setAttribute('data-word', originalWords[i]);
            addedNodes.push(spannode);

            middlebit = node.splitText(pos);
            endbit = middlebit.splitText(words[i].length);
            middleclone = middlebit.cloneNode(true);
            spannode.appendChild(middleclone);

            middlebit.parentNode.replaceChild(spannode, middlebit);

            procNode.unshift(endbit);
          }
        }
      } else if (node.nodeType === 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
        children = $.makeArray(node.childNodes);
        for (i = 0; i < children.length; i++) {
          procNode.unshift(children[i]);
        }
      }
    }
  }
}
