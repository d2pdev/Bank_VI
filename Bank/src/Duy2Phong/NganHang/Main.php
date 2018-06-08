<?php

namespace Duy2Phong\NganHang;
/*
 *
 * @author Duy2Phong
 * @link https://poggit.pmmp.io/ci/d2pdev/Bank_VI/Bank
 *
 *
*/
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class Main extends PluginBase{
   public function onEnable()
    {
			$this->getLogger()->info("§2Đã bật !");
        if(!is_dir($this->getDataFolder()))
	{
        mkdir($this->getDataFolder());
        }
        $this->nganhang = new Config($this->getDataFolder() ."nganhang.yml", Config::YAML, []);
        $this->eco = EconomyAPI::getInstance();
    }
	public function taoNguoiDung($ten){
    $ten = strtolower($ten);
		$this->nganhang->set($ten,0);
		$this->nganhang->save();
	}
	public function congTien($ten,$tien){
    $ten = strtolower($ten);
		$tienhienco = $this->nganhang->get($ten);
		$this->nganhang->set($ten,$tienhienco + $tien);
		$this->nganhang->save();
	}
	public function truTien($ten,$tien){
    $ten = strtolower($ten);
		$this->congTien($ten,-$tien);
	}
	public function caiTien($ten,$tien){
    $ten = strtolower($ten);
		$this->nganhang->set($ten,$tien);
		$this->nganhang->save();
	}
	public function xemTien($ten){
    $ten = strtolower($ten);
		if($this->kiemTra($ten)){
		$tienhienco = $this->nganhang->get($ten);
		return $tienhienco;
		}
	    return false;
	}
	public function kiemTra( $ten){
    $ten = strtolower($ten);
		if($this->nganhang->exists($ten)){
			return true;
		}
		return false;
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $ar) : bool{
		switch($command->getName()){
			case "nganhang":
				if(isset($ar[0])){
					$ten = $sender->getName();
					$all = $this->nganhang->getAll();
					$money = $this->eco->myMoney($ten);
					if(!$this->kiemTra($ten)){
					$this->taoNguoiDung($ten);
					}
					if($ar[0] == 'xemtien'){
						$tienhienco = $this->xemTien($ten);
						$sender->sendMessage("§fSố tiền hiện có trong ngân hàng là §a$tienhienco");
						return true;
					}
					if($ar[0] == 'version' or $ar[0] == 'ver'){
						$sender->sendMessage('§f-> §2Ngân Hàng§f <-');
						$sender->sendMessage('Phiên bản hiện tại : §e1.1.1');
						$sender->sendMessage('author : Duy2Phong ');
					  $sender->sendMessage('Cập nhật phiên bản mới nhất tại : https://poggit.pmmp.io/ci/d2pdev/Bank_VI/Bank');
						return true;
					}
					if($ar[0] == 'help'){
						$sender->sendMessage('§2===Ngân Hàng===');
						$sender->sendMessage('/nganhang guitien money (gửi tiền vào ngân hàng)');
						$sender->sendMessage('/nganhang ruttien money (rút tiền ra từ ngân hàng)');
						$sender->sendMessage('/nganhang chuyentien money nguoinhan (chuyển tiền từ ngân hàng cho người chơi khác)');
						$sender->sendMessage('/nganhang xemtien (xem số tiền hiện có trong ngân hàng)');
						$sender->sendMessage('/nganhang version (xem phiên bản của ngân hàng)');
						$sender->sendMessage('§2===============');
						return true;
					}

					if(isset($ar[1])){
						$tien = $ar[1];
						if(!is_numeric($tien)){
							$sender->sendMessage('');
							return false;
						}
						$tien = round($tien,3);
						switch($ar[0]){
							case "guitien":
							if($money >= $tien){
								$this->congTien($ten,$tien);
								$this->eco->reduceMoney($ten, $tien);
								$sender->sendMessage("§fBạn đã gửi §a$tien §fvào ngân hàng !");
								return true;
							}
							$sender->sendMessage("§cSố tiền bạn gửi nhiều hơn số tiền bạn hiện có !");
							break;
							case "ruttien":
							if($this->xemTien($ten) >= $tien){
								$this->truTien($ten,$tien);
								$this->eco->addMoney($ten,$tien);
								$tien = (string)$tien;
								$sender->sendMessage("§fBạn đã lấy ra §a$tien §ftừ ngân hàng !");
								return true;
							}
							else
								$sender->sendMessage("§Số tiền bạn rút nhiều hơn số tiền bạn hiện có !");
							break;
              case "chuyentien":
                if($this->kiemTra($ten)){
                  if($this->xemTien($ten) >= strtolower($tien)){
                    if(isset($ar[2])){
                      $this->truTien($ten,$tien);
                      $this->congTien($ar[2],$tien);
                        foreach($this->getServer()->getOnlinePlayers() as $p){
                          if(strtolower($ar[2]) == strtolower($p->getName())){
                            $nguoinhan = $p;
                            break;
                          }
                        }
                      if(isset($nguoinhan)){
                        $nguoinhan->sendMessage("$ten §fđã chuyển cho bạn §a$t");
                        return true;
                      }
                      $sender->sendMessage("$ar[2] §fhiện đang không online nhưng số tiền đã được chuyển thành công !");
                      return true;
                    }
                  }
                  $sender->sendMessage("§eSố tiền trong tài khoảng của bạn không đủ để thực hiện giao dịch nầy !");
                  return true;
                }
                $sender->sendMessage("$ar[2] §ckhông tồn tại trong dữ liệu của ngân hàng !");
              break;
						}
					}
				}
			break;
			default:
				break;
		}
    return false;
	}

	public function onDisable(){
		$this->getLogger()->info("§cĐã dừng !");
	}
}
