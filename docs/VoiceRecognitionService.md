# Voice Recognition Service setup


First of all, the voice recognition service rely on Pocketsphinx. 

## Compile and install Pocketsphinx

```bash
mkdir ~/cmusphinx
cd ~/cmusphinx
wget http://downloads.sourceforge.net/project/cmusphinx/sphinxbase/0.8/sphinxbase-0.8.tar.gz
wget http://downloads.sourceforge.net/project/cmusphinx/pocketsphinx/0.8/pocketsphinx-0.8.tar.gz 
tar -zxf sphinxbase-0.8.tar.gz
tar -zxf pocketsphinx-0.8.tar.gz 

cd ~/cmusphinx/sphinxbase-0.8
./configure
make
sudo make install

cd ~/cmusphinx/pocketsphinx-0.8
./configure
make
sudo make install
```

## Configure bots.json

Add this to the bots.json "services" array:

```json
        {
        	"Pkj.AutomationAI.Services.VoiceRecognitionService": {
        		"pocketsphinx_binary": "/home/peec/cmusphinx/pocketsphinx-0.8/src/programs/"
        	}
        }
```



