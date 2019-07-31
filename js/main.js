
function CRFS(object)
{   
    this.yesButtonClicked = function(ev)
    {
        if(this.state == 0)
        {
            this.state = 1;
            this.murpleType = 0;
            this.murpleIdx  = 0;
            this.noButton.style.display = 'block';
            this.next.setAttribute('showRotate', 0);
            this.showMurps();
            $("[tileid=-2]").css('visibility', 'hidden');
           
        }
        else
        {
            this.state = 0;
            this.yesButton.style.display = 'none';
            this.noButton.style.display = 'none';
                    
            $.get( "php/set.php", 
                    { 
                        XDEBUG_SESSION_START: 'netbeans',
                        a: 1,
                        x: this.currentX,
                        y: this.currentY,
                        rotation: _this.next.getAttribute('rotate'),
                        meepleType: _this.murpleType,
                        meeplePosition: _this.murpleIdx
                    }).done(_this.startTicks()).fail(function() { this.showError( "error" ); });
                    
            this.resetNext = true;
        }
    }.bind(this);
    
    this.noButtonClicked = function(ev)
    {
        this.noButton.style.display = 'none';
        this.state = 0;
        if(this.currentAttributes.length > 1)
           this.next.setAttribute('showRotate', 1);
        $(this.next).find('.murp').remove();
        $("[tileid=-2]").css('visibility', 'visible');
    }.bind(this);
    
    this.hasMurpType = function(type)
    {
        switch(type)
        {
            case 1:
            case 3:
                return ((this.status >> 15) & 0xF) > 0;
            case 2:
            case 6:
                return ((this.status >> 19) & 0x1) > 0;
            case 4:
                return ((this.status >> 20) & 0x1) > 0;
            case 5:
                return ((this.status >> 21) & 0x1) > 0;
        }
        return false;
    }.bind(this);
    
    this.showMurps = function()
    {
        var rotate = this.next.getAttribute('rotate');
        for(var i = 0; i < this.currentAttributes.length; i++)
        {
            if(rotate == (this.currentAttributes[i] >> 29))
            {
                var attributes = this.currentAttributes[i] & 0x1FFFFFFF;
                var idx = 0;
                while(attributes > 0)
                {
                    if(this.hasMurpType(attributes & 0x7))
                    {
                        var el = document.createElement('div');
                        el.className = 'murp'
                        el.setAttribute('type', '0');
                        el.setAttribute('color', this.players[this.sequence]);
                        el.setAttribute('holderType', attributes & 0x7);
                        el.setAttribute('idx', idx);
                        
                        $(el).on("click", this.murpleClicked);

                        this.next.appendChild(el);
                    }
                    attributes = (attributes >> 3);
                    idx++;
                }
            }
        }
        
    }
    
    this.murpleClicked = function(ev)
    {
        var holderType = 0;
        if(this.murpleDiv == ev.currentTarget)
        {
            var type = ev.currentTarget.getAttribute('type');
            if(type == 1 && ((this.status >> 19) & 0x1) > 0)
                holderType = 2;
            else if(type == 3 && ((this.status >> 19) & 0x1) > 0)
                holderType = 6;
            else
                this.murpleDiv = null;
        }
        else 
        {
            if(this.murpleDiv)
                this.murpleDiv.setAttribute('type', 0);
            holderType = ev.currentTarget.getAttribute('holderType');
            this.murpleDiv = ev.currentTarget;
        }
        
        ev.currentTarget.setAttribute('type', holderType);
        
        if(holderType > 0)
        {
            this.murpleType = holderType;
            this.murpleIdx  = ev.currentTarget.getAttribute('idx');
        }
        else
        {
            this.murpleType = 0;
            this.murpleIdx  = 0;
        }
    }.bind(this);
    
    this.rotate = function(ev)
    {
        var element = ev.currentTarget.parentNode;
        var rotate = element.getAttribute('rotate');
        for(var i = 0; i < this.currentAttributes.length; ++i)
        {
            if(rotate == (this.currentAttributes[i] >> 29))
            {
                i = (i+1)%this.currentAttributes.length;
                element.setAttribute('rotate', this.currentAttributes[i] >> 29);
                break;
            }
        }
    }.bind(this);
    
    this.selectNext = function(ev)
    {
        var element = ev.currentTarget;
        
        if(this.hidden)
        {
            this.hidden.style.visibility = "visible";
            this.hidden.setAttribute("id", "");
        }
        this.next.style.top = element.style.top;
        this.next.style.left = element.style.left;
        this.next.setAttribute('preview', 0);
        element.style.visibility = "hidden";
        this.hidden = element;
        this.currentX = element.getAttribute('x');
        this.currentY = element.getAttribute('y');
        element.parentNode.appendChild(this.next);
        
        this.currentAttributes = element.getAttribute('positions').split(',');
        this.next.setAttribute('rotate', this.currentAttributes[0] >> 29);
        if(this.currentAttributes.length == 1)
            this.next.setAttribute('showRotate', 0);
        else
            this.next.setAttribute('showRotate', 1);
        
        this.yesButton.style.display = 'block';
    }.bind(this);
    
    this.displayPlays = function(data)
    {
        if(this.debug)
            this.debug.innerHTML = data;
        var positions = JSON.parse(data);
        
        for(var col in positions)
        {
            for(var row in positions[col])
            {
                var cell = positions[col][row];
                var el = document.createElement("div");
                el.className = 'tile';
                el.setAttribute('tileId', -2);
                el.setAttribute('x', col);
                el.setAttribute('y', row);
                el.style.left = (130 * col) + "px";
                el.style.top = (130 * row) + "px";
                el.setAttribute('positions', cell);
                
                $(el).on("click", this.selectNext);

                this.obj.appendChild(el);
            }
        }
    }.bind(this);
    
    /*
    this.test = function(ev)
    {
        if(ev.ctrlKey)
            console.log('next'+ev.currentTarget.attributes[1].nodeValue);
        if(ev.shiftKey)
            console.log("farm"+ev.offsetX + ":" + ev.offsetY);
        else
            console.log(ev.offsetX + ":" + ev.offsetY);
        
    }
     */
    
    this.setRefreshesHelper = function(arr, h)
    {
        for(var j = 0; j < arr.length;)
        {
            var col = arr[j++];
            var row = arr[j++];

            var element = $("div[x=" + col + "][y=" + row + "]");
            if(element.length === 0)
                continue;
            element[0].setAttribute('h', h);
        }
    }
    
    this.setRefreshesMeepleHelper = function(arr, scoring)
    {
        for(var j = 0; j < arr.length;)
        {
            var col = arr[j++];
            var row = arr[j++];

            var element = $("div[x=" + col + "][y=" + row + "]");
            if(element.length === 0)
                continue;
            
            var element = element[0];
            if(element.childElementCount === 0)
                continue;
            
            element.removeChild(element.firstChild);
        }
    };
    
    this.setRefreshes = function(data)
    {
        if(data.length <= 0)
            return;
        
        var sets = data.split(';');
        for(var i = 0; i < sets.length;)
        {
            var refresh = sets[i++].split(',');
            var hit     = sets[i++].split(',');
            var scoring = sets[i++].split(',');
            
            this.setRefreshesHelper(refresh, 1);
            this.setRefreshesHelper(hit, 1);
            $("div[h=0]").fadeOut(1500);
            this.setRefreshesMeepleHelper(refresh, scoring);
            $("div[h=0]").fadeIn(1500);
            this.setRefreshesHelper(refresh, 0);
            this.setRefreshesHelper(hit, 0);
            
        }
    };
    
    this.displayBoard = function(data)
    {
        if(data.length <= 0)
            return;
        
        var board = data.split(',');
        for(var i = 0; i < board.length;)
        {
            var col = board[i++];
            var row = board[i++];
            var cell = board[i++];
            var el = document.createElement("div");
            el.className = 'tile';
            el.setAttribute('x', col);
            el.setAttribute('y', row);
            el.setAttribute('h', 0);
            el.setAttribute('tileId', (cell >> 10) & 0xFF);
            el.style.left = (130 * col) + "px";
            el.style.top = (130 * row) + "px";
            el.setAttribute('rotate', (cell >> 18));
            if(cell & 0x3FF)
            {
                var murp = document.createElement("div");
                murp.className = 'murp';
                murp.setAttribute('color', this.players[cell & 0xF]);
                murp.setAttribute('type', (cell & 0x70) >> 4);
                murp.setAttribute('idx', (cell & 0x380) >> 7);

                el.appendChild(murp);
            }

            this.obj.appendChild(el);
            
            this.boardSequence++;
        }
            
            /*
        for(var i = 1; i < 78; i++)
        {
            var el = document.createElement("div");
            el.className = 'tile';
            el.setAttribute('tileId', i);
            el.style.left = (130 * ((i-1)%8)) + "px";
            el.style.top = (130 * parseInt((i-1)/8)) + "px";
            el.setAttribute('rotate', this.board[i*5 + 3]);
            el.innerHTML = i;
            for(var j = 0; j < 9; j++)
            {
                var murp = document.createElement("div");
                var text = document.createElement("div");
                murp.appendChild(text);
                text.innerHTML = j;
                murp.className = 'murp';
                murp.setAttribute('type', 0);
                murp.setAttribute('idx', j);

                el.appendChild(murp);
            }
            
            el.onclick = this.test;
            this.obj.appendChild(el);
            
            
        }
            */
        this.currentBoardSize = this.board.length;
    };
    
    this.displayPlayers = function(data)
    {
        var status = data.split(',');
        var j = 0;
        
        this.scoreBoard.children().remove();
        this.players = [];
        
        for(var i = 0; i < status.length;)
        {
            var sequence = (i/3);
            var player = document.createElement("div");
            player.className = "player";
            player.style['left'] = sequence * 160 + 'px';
            
            var stat = status[i++];
            
            if(this.sequence == sequence)
                this.status = stat;
            
            var color = status[i++];
            this.players[sequence] = color;
            
            var icon = document.createElement("div");
            icon.className = 'icon';
            icon.style.backgroundImage = "url('images/players/" + status[i++] + "')";
            player.appendChild(icon);
            
            var trade = document.createElement("div");
            trade.className = 'trade';
            var barrel = document.createElement("div");
            barrel.innerHTML = (stat >> 22) & 0xF;
            trade.appendChild(barrel);
            var grain = document.createElement("div");
            grain.innerHTML = (stat >> 26) & 0x7;
            trade.appendChild(grain);
            var silk = document.createElement("div");
            silk.innerHTML = (stat >> 29) & 0x7;
            trade.appendChild(silk);
            player.appendChild(trade);
            
            var meeps = document.createElement("div");
            meeps.className = 'meeps';
            meeps.innerHTML = (stat >> 15) & 0xF;
            player.appendChild(meeps);
            
            var score = document.createElement("div");
            score.className = 'score';
            score.innerHTML = (stat & 0x7FFF);
            player.appendChild(score);
            
            var pawns = document.createElement("div");
            pawns.className = 'pawns';
            var meepmeep = document.createElement("div");
            meepmeep.className = 'meepmeep';
            if(((stat >> 19) & 1) == 0)
                meepmeep.setAttribute('state', 0);
            pawns.appendChild(meepmeep);
            var builder = document.createElement("div");
            builder.className = 'builder';
            if(((stat >> 21) & 1) == 0)
                builder.setAttribute('state', 0);
            pawns.appendChild(builder);
            var pig = document.createElement("div");
            pig.className = 'pig';
            if(((stat >> 20) & 1) == 0)
                pig.setAttribute('state', 0);
            pawns.appendChild(pig);
            player.appendChild(pawns);
            
            this.scoreBoard.append(player);
        }
    };
    
    this.displayCurrentTile = function(data)
    {
        this.next.setAttribute('tileid', data);
        this.next.style.display = 'block';
    };
    
    this.setBoard = function(data)
    {
        if(!this.verifyData(data, 1))
            return;
        data = data.substr(1);
        
        if(this.resetNext)
        {
            $("[tileid=-2]").remove();
            $(this.next).find('.murp').remove();
            this.buttons.prepend(this.next);
            this.next.style.display = 'none';
            this.next.style.top = '';
            this.next.style.left = '';
            this.next.setAttribute('preview', 1);
        }
        
        var dataSplit = data.split('#');
        var idx = dataSplit.length - 1;
        this.displayPlayers(dataSplit[idx--]);
        this.displayCurrentTile(dataSplit[idx--]);
        this.displayBoard(dataSplit[idx--]);
        this.setRefreshes(dataSplit[idx--]);
        if(idx >= 0)
            this.displayPlays(dataSplit[idx--]);
        else
            this.startTicks();
        
    }.bind(this);
    
    this.ticked = function(data)
    {
        if(!this.verifyData(data))
            return;
        data = data.substr(1);
        
        if(data != _this.currentSequence)
        {
            this.stopTicks();
            _this.currentSequence = parseInt(data);
            $.get( "php/get.php", { a: (this.sequence==this.currentSequence)?1:2, sequence: this.boardSequence })
            .done(_this.setBoard).fail(function() { this.showError( "error" ); });
        }
    }.bind(this);
    
    this.tick = function(data)
    {
        if(this.debug)
            this.debug.innerHTML = data;
        $.get( "php/get.php", { })
        .done(_this.ticked).fail(function() { this.showError( "error" ); });
        /*.always(function() { alert( "finished" ); });
        // Perform other work here ...

        // Set another completion function for the request above
        jqxhr.always(function() {
        alert( "second finished" );
        });
*/
    };
    
    this.showError = function(errorm)
    {
        if(this.debug)
            this.debug.innerHTML = errorm;
        else
            this.error = errorm;
    }.bind(this);
    
    this.verifyData = function(data, type)
    {
        if(this.debug)
            this.debug.innerHTML = data;
        if(!data || !data.length > 1)
        {
            this.stopTicks();
            this.error = 'No data';
            return false;
        }
        
        if(data.charAt(0) != '0')
        {
            this.stopTicks();
            this.error = data.substr(1);
            return false;
        }
        
        data = data.substr(1);
        
        if(!type)
            return true;
        
        switch(type)
        {
            case 1:
                if(data.indexOf('#') < 0)
                {
                    this.error = 'Bad data: ' + data;
                    this.stopTicks();
                    return false;
                }
        }
        
        return true;
    };
    
    this.startTicks = function()
    {
        if(this.interval === null)
        {
            this.interval = window.setInterval(this.tick, 2500);
            this.tick();
        }
    };
    
    this.stopTicks = function()
    {
        window.clearInterval(this.interval);
        this.interval = null;
    };
    
    var _this = this;
    
    this.boardSequence = -1;
    
    
    this.jObj = object;
    this.obj = object[0];
    this.session = this.obj.getAttribute("session");
    this.wait = true;
    this.player = this.obj.getAttribute("playerId");
    this.players = [];
    this.sequence = parseInt(this.obj.getAttribute("sequence"));
    this.currentSequence = -1;
    this.board = [];
    this.currentBoardSize = 0;
    this.currentAttributes = [];
    this.hidden = null;
    this.currentX = null;
    this.currentY = null;
    this.murpleType = -1;
    this.murpleIdx = -1;
    this.resetNext = false;
    this.scoreBoard = $('.scoreBoard');
    this.status = 0;
    this.debug = $("#debug");
    if(this.debug)
        this.debug = this.debug[0];
    this.error = $("#error");
    if(this.error)
        this.error = this.error[0];
    
    this.next = $( "#next" )[0];
    this.next.firstElementChild.onclick = this.rotate;
    this.murpleDiv = this.next.lastElementChild;
    this.yesButton = $("#playYes")[0];
    this.yesButton.onclick = this.yesButtonClicked;
    this.noButton = $("#playNo")[0];
    this.noButton.onclick = this.noButtonClicked;
    this.buttons = $("#buttons")[0];
    
    this.state = 0;
    this.interval = null;
    
    this.startTicks();
}



/*
 * Board sturcture:
 * [0] = pieceId
 * [3] = rotation
 * [4] = murple
 *          0 - 3: playerId
 *          4 - 6: type
 *          7 - 9: position
 * 
            // 0: nothing
            // 1: meeple
            // 2: meepmeep
            // 3: farmer
            // 4: piggy
            // 5: builder
            // 6: farmerfarmer
 * Progress:
 * [0] = next piece id
 * [1] = # players
 * [2] = meeple status player 1
 *          0 -14:  score
 *          15-18:  meeple
 *          19:     meepmeep
 *          20:     pig
 *          21:     builder
 *          22-25:  barrels
 *          26-28:  wheat
 *          29-31:  silk
 * [3] = meeple status player 2
 * [4] = meeple status player 2
 * ...
 * [0] = #positions
 * [2] = posX 
 * [3] = posY
 * [4] = rotation & meeple positions 1
 *          0 - 10: positions for rotation 1
 *          16- 31: positions for rotation 2
 * [5] = rotation & meeple positions 2
 *          0 - 10: positions for rotation 3
 *          16- 31: positions for rotation 4
 */